<?php
// app/Controllers/PlayerSelfRegister.php
namespace App\Controllers;

use App\Libraries\EmailHelper;

class PlayerSelfRegister extends BaseController
{
    // ── GET /player-register ─────────────────────────────────
    // Step 1: show email entry form
    public function index()
    {
        return view('players/self_register/step1_email');
    }

    // ── POST /player-register/send-otp ───────────────────────
    public function sendOtp()
    {
        $rules = ['email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $email = strtolower(trim($this->request->getPost('email')));

        // Check if already registered as a player with this email
        $existing = $this->db->table('players')->where('email', $email)->where('status !=', 'Inactive')->get()->getRowArray();
        if ($existing) {
            return redirect()->back()->with('error', 'This email is already registered. Please login.')->withInput();
        }

        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Delete any old OTPs for this email
        $this->db->table('player_otp_verifications')->where('email', $email)->delete();

        $this->db->table('player_otp_verifications')->insert([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => $expires,
        ]);

        $sent = (new EmailHelper())->sendOtp($email, $otp);

        if (!$sent) {
            // In dev: log OTP so you can test without real SMTP
            log_message('info', "JSCA OTP for {$email}: {$otp}");
        }

        session()->set('otp_email', $email);
        return redirect()->to('player-register/verify-otp')
            ->with('success', 'OTP sent to ' . $email . '. Check your inbox (or server logs in dev).');
    }

    // ── GET /player-register/verify-otp ─────────────────────
    // Step 2: enter OTP
    public function verifyOtpForm()
    {
        if (!session()->get('otp_email')) {
            return redirect()->to('player-register');
        }
        return view('players/self_register/step2_otp', [
            'email' => session()->get('otp_email'),
        ]);
    }

    // ── POST /player-register/verify-otp ────────────────────
    public function verifyOtp()
    {
        $email = session()->get('otp_email');
        if (!$email) return redirect()->to('player-register');

        $rules = ['otp' => 'required|exact_length[6]|numeric'];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $otp = $this->request->getPost('otp');

        $record = $this->db->table('player_otp_verifications')
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('verified', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$record) {
            return redirect()->back()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        // Mark OTP as verified
        $this->db->table('player_otp_verifications')->where('id', $record['id'])->update(['verified' => 1]);

        // Store verified flag in session
        session()->set('otp_verified_email', $email);
        session()->remove('otp_email');

        return redirect()->to('player-register/form');
    }

    // ── GET /player-register/form ────────────────────────────
    // Step 3: registration form
    public function form()
    {
        $email = session()->get('otp_verified_email');
        if (!$email) return redirect()->to('player-register');

        return view('players/self_register/step3_form', [
            'email'     => $email,
            'districts' => $this->db->table('districts')->where('is_active', 1)->orderBy('name')->get()->getResultArray(),
        ]);
    }

    // ── POST /player-register/submit ────────────────────────
    public function submit()
    {
        $email = session()->get('otp_verified_email');
        if (!$email) return redirect()->to('player-register');

        $rules = [
            'full_name'      => 'required|min_length[3]|max_length[100]',
            'date_of_birth'  => 'required|valid_date[Y-m-d]',
            'gender'         => 'required|in_list[Male,Female,Other]',
            'district_id'    => 'required|is_natural_no_zero',
            'role'           => 'required|in_list[Batsman,Bowler,All-rounder,Wicket-keeper]',
            'phone'          => 'required|regex_match[/^[6-9][0-9]{9}$/]',
            'aadhaar_number' => 'permit_empty|exact_length[12]|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors())->withInput();
        }

        $post = $this->request->getPost();

        $age = (int)date_diff(date_create($post['date_of_birth']), date_create('now'))->y;
        $ageCategory = match (true) {
            $age < 14 => 'U14',
            $age < 16 => 'U16',
            $age < 19 => 'U19',
            $age < 40 => 'Senior',
            default   => 'Masters',
        };

        // Photo upload
        $photoPath = null;
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $ext      = strtolower($photo->getClientExtension());
            $dir      = FCPATH . 'assets/uploads/players';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $name     = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $photo->move($dir, $name);
            $photoPath = 'assets/uploads/players/' . $name;
        }

        // Generate JSCA Player ID
        $year    = date('Y');
        $count   = $this->db->table('players')->countAllResults() + 1;
        $jscaId  = 'JSCA-P-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        // Generate a random password
        $plainPassword = $this->generatePassword();

        // Check duplicate email in users table before inserting
        $emailExists = $this->db->table('users')->where('email', $email)->countAllResults();
        if ($emailExists) {
            return redirect()->back()->with('error', 'This email is already registered. Please login.')->withInput();
        }

        // Create user account (inactive until admin verifies)
        $this->db->table('users')->insert([
            'role_id'       => $this->getDefaultPlayerRoleId(),
            'full_name'     => $post['full_name'],
            'email'         => $email,
            'phone'         => $post['phone'] ?? null,
            'password_hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'is_active'     => 0, // inactive until verified
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $userId = $this->db->insertID();

        $addressParts = array_filter([
            $post['address_line1'] ?? '',
            $post['city']          ?? '',
            $post['state']         ?? '',
            !empty($post['pin_code']) ? 'PIN: ' . $post['pin_code'] : '',
        ]);

        $data = [
            'jsca_player_id'    => $jscaId,
            'full_name'         => $post['full_name'],
            'date_of_birth'     => $post['date_of_birth'],
            'gender'            => $post['gender'],
            'age_category'      => $ageCategory,
            'district_id'       => $post['district_id'],
            'role'              => $post['role'],
            'batting_style'     => $post['batting_style'] ?? null,
            'bowling_style'     => $post['bowling_style'] ?? 'N/A',
            'aadhaar_number'    => $post['aadhaar_number'] ?? null,
            'phone'             => $post['phone'] ?? null,
            'email'             => $email,
            'address'           => implode(', ', $addressParts) ?: null,
            'guardian_name'     => $post['guardian_name'] ?? null,
            'guardian_phone'    => $post['guardian_phone'] ?? null,
            'photo_path'        => $photoPath,
            'status'            => 'Inactive', // pending admin verification
            'registration_type' => 'self',
            'user_id'           => $userId,
            'registered_by'     => null,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('players')->insert($data);
        $playerId = $this->db->insertID();

        $this->db->table('player_career_stats')->insert(['player_id' => $playerId]);

        // Send credentials email
        (new EmailHelper())->sendPlayerCredentials($email, $post['full_name'], $jscaId, $plainPassword);

        // Clear session
        session()->remove('otp_verified_email');

        return redirect()->to('player-register/success')
            ->with('success_name', $post['full_name'])
            ->with('success_id', $jscaId);
    }

    // ── GET /player-register/success ────────────────────────
    public function success()
    {
        return view('players/self_register/success');
    }

    // ── Private helpers ───────────────────────────────────────

    private function generatePassword(int $length = 10): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
        $pw    = '';
        for ($i = 0; $i < $length; $i++) {
            $pw .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pw;
    }

    private function getDefaultPlayerRoleId(): int
    {
        // Use 'data_entry' role or create a 'player' role if exists
        $role = $this->db->table('roles')->where('name', 'player')->get()->getRowArray();
        if ($role) return (int)$role['id'];

        // Fallback: selector role (read-only)
        $role = $this->db->table('roles')->where('name', 'selector')->get()->getRowArray();
        return $role ? (int)$role['id'] : 3;
    }
}
