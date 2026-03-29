<?php
// app/Controllers/Auth.php
namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ── GET /login ───────────────────────────────────────────
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'pageTitle' => 'Login — JSCA ERP',
        ]);
    }

    // ── POST /login ──────────────────────────────────────────
    public function doLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->db->table('users u')
            ->select('u.*, r.name as role_name, r.permissions')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.email', $email)
            ->get()->getRowArray();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->with('error', 'Invalid email or password.')
                ->withInput();
        }

        if (!$user['is_active']) {
            return redirect()->back()
                ->with('error', 'Your account has been disabled. Contact administrator.');
        }

        // Load allowed districts for this user
        $districtIds = [];
        if ($user['role_name'] !== 'superadmin') {
            $rows = $this->db->table('user_districts')
                ->select('district_id')
                ->where('user_id', $user['id'])
                ->get()->getResultArray();
            $districtIds = array_map('intval', array_column($rows, 'district_id'));
        }

        // Regenerate session ID to avoid fixation, then set data
        session()->regenerate(true);
        session()->set([
            'user_id'              => $user['id'],
            'user_name'            => $user['full_name'],
            'user_role'            => $user['role_name'],
            'user_email'           => $user['email'],
            'allowed_district_ids' => $districtIds,
        ]);

        // Update last login
        $this->db->table('users')
            ->where('id', $user['id'])
            ->update(['last_login' => date('Y-m-d H:i:s')]);

        $this->audit('LOGIN', 'auth', $user['id']);

        $redirectUrl = session()->get('redirect_url') ?? '/dashboard';
        session()->remove('redirect_url');

        // Role-based redirect
        $roleName = $user['role_name'];
        if (in_array($roleName, ['umpire', 'scorer', 'referee', 'match_referee'])) {
            $redirectUrl = '/official/dashboard';
        } elseif ($roleName === 'player') {
            $redirectUrl = '/player/dashboard';
        } elseif (session()->get('redirect_url')) {
            $redirectUrl = session()->get('redirect_url');
        } else {
            $redirectUrl = '/dashboard';
        }
        session()->remove('redirect_url');

        return redirect()->to($redirectUrl)
            ->with('success', 'Welcome back, ' . $user['full_name'] . '!');
    }

    // ── GET /logout ──────────────────────────────────────────
    public function logout()
    {
        $this->audit('LOGOUT', 'auth', session('user_id'));
        session()->destroy();
        return redirect()->to('/login')
            ->with('success', 'You have been logged out successfully.');
    }

    // ── GET /forgot-password ─────────────────────────────────
    public function forgotPassword()
    {
        return view('auth/forgot_password', ['pageTitle' => 'Forgot Password — JSCA ERP']);
    }

    // ── POST /forgot-password ────────────────────────────────
    public function sendReset()
    {
        $email = $this->request->getPost('email');
        $user  = $this->db->table('users')->where('email', $email)->get()->getRowArray();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->db->table('users')->where('id', $user['id'])->update([
                'reset_token'   => $token,
                'reset_expires' => $expires,
            ]);

            // In production — send email here
            // \Config\Services::email()->...
            log_message('info', "Password reset token for {$email}: {$token}");
        }

        // Always show success to prevent email enumeration
        return redirect()->back()
            ->with('success', 'If that email exists, a reset link has been sent.');
    }

    // ── GET /reset-password/:token ───────────────────────────
    public function resetPassword(string $token)
    {
        $user = $this->db->table('users')
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to('/login')
                ->with('error', 'Password reset link is invalid or has expired.');
        }

        return view('auth/reset_password', [
            'pageTitle' => 'Reset Password — JSCA ERP',
            'token'     => $token,
        ]);
    }

    // ── POST /reset-password/:token ──────────────────────────
    public function doReset(string $token)
    {
        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->db->table('users')
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Invalid or expired token.');
        }

        $this->db->table('users')->where('id', $user['id'])->update([
            'password_hash'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'reset_token'    => null,
            'reset_expires'  => null,
        ]);

        return redirect()->to('/login')
            ->with('success', 'Password reset successfully. Please log in.');
    }
}
