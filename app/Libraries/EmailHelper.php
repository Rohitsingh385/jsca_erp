<?php
// app/Libraries/EmailHelper.php
namespace App\Libraries;

class EmailHelper
{
    private \CodeIgniter\Email\Email $mailer;

    public function __construct()
    {
        $this->mailer = \Config\Services::email();
        $this->mailer->initialize([
            'protocol'  => env('email.protocol', 'smtp'),
            'SMTPHost'  => env('email.SMTPHost', ''),
            'SMTPUser'  => env('email.SMTPUser', ''),
            'SMTPPass'  => env('email.SMTPPass', ''),
            'SMTPPort'  => (int) env('email.SMTPPort', 587),
            'SMTPCrypto'=> env('email.SMTPCrypto', 'tls'),
            'mailType'  => 'html',
            'charset'   => 'UTF-8',
            'fromEmail' => env('email.fromEmail', 'noreply@jsca.in'),
            'fromName'  => env('email.fromName', 'JSCA ERP'),
        ]);
    }

    public function sendOtp(string $toEmail, string $otp): bool
    {
        $this->mailer->setTo($toEmail);
        $this->mailer->setSubject('JSCA — Email Verification OTP');
        $this->mailer->setMessage($this->otpTemplate($toEmail, $otp));
        return $this->mailer->send(false);
    }

    public function sendPlayerCredentials(string $toEmail, string $name, string $jsca_id, string $password): bool
    {
        $this->mailer->setTo($toEmail);
        $this->mailer->setSubject('JSCA — Your Player Account Credentials');
        $this->mailer->setMessage($this->credentialsTemplate($name, $jsca_id, $password));
        return $this->mailer->send(false);
    }

    public function sendAccountActivated(string $toEmail, string $name, string $jsca_id): bool
    {
        $this->mailer->setTo($toEmail);
        $this->mailer->setSubject('JSCA — Your Player Account Has Been Activated');
        $this->mailer->setMessage($this->activatedTemplate($name, $jsca_id));
        return $this->mailer->send(false);
    }

    // ── Templates ─────────────────────────────────────────────

    private function otpTemplate(string $email, string $otp): string
    {
        return <<<HTML
        <div style="font-family:Segoe UI,sans-serif;max-width:480px;margin:auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;">
          <div style="background:#1a3a5c;padding:20px 24px;">
            <h2 style="color:#fff;margin:0;font-size:18px;">🏏 JSCA ERP — Email Verification</h2>
          </div>
          <div style="padding:24px;">
            <p style="color:#333;">Use the OTP below to verify your email address. It expires in <strong>10 minutes</strong>.</p>
            <div style="text-align:center;margin:24px 0;">
              <span style="font-size:36px;font-weight:800;letter-spacing:10px;color:#1a3a5c;">{$otp}</span>
            </div>
            <p style="color:#888;font-size:12px;">If you did not request this, please ignore this email.</p>
          </div>
        </div>
        HTML;
    }

    private function credentialsTemplate(string $name, string $jsca_id, string $password): string
    {
        $loginUrl = base_url('login');
        return <<<HTML
        <div style="font-family:Segoe UI,sans-serif;max-width:480px;margin:auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;">
          <div style="background:#1a3a5c;padding:20px 24px;">
            <h2 style="color:#fff;margin:0;font-size:18px;">🏏 JSCA ERP — Player Account Created</h2>
          </div>
          <div style="padding:24px;">
            <p style="color:#333;">Dear <strong>{$name}</strong>,</p>
            <p>Your JSCA player account has been created. Here are your login credentials:</p>
            <table style="width:100%;border-collapse:collapse;margin:16px 0;">
              <tr><td style="padding:8px;background:#f8f9fa;font-weight:600;width:40%;">JSCA Player ID</td><td style="padding:8px;background:#f8f9fa;">{$jsca_id}</td></tr>
              <tr><td style="padding:8px;font-weight:600;">Password</td><td style="padding:8px;">{$password}</td></tr>
            </table>
            <p style="color:#e74c3c;font-size:12px;"><strong>Please change your password after first login.</strong></p>
            <a href="{$loginUrl}" style="display:inline-block;background:#1a3a5c;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;margin-top:8px;">Login to JSCA ERP</a>
          </div>
        </div>
        HTML;
    }

    private function activatedTemplate(string $name, string $jsca_id): string
    {
        $loginUrl = base_url('login');
        return <<<HTML
        <div style="font-family:Segoe UI,sans-serif;max-width:480px;margin:auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;">
          <div style="background:#2ecc71;padding:20px 24px;">
            <h2 style="color:#fff;margin:0;font-size:18px;">🏏 JSCA ERP — Account Activated!</h2>
          </div>
          <div style="padding:24px;">
            <p style="color:#333;">Dear <strong>{$name}</strong>,</p>
            <p>Your JSCA player account (<strong>{$jsca_id}</strong>) has been <strong>verified and activated</strong> by the JSCA administration.</p>
            <p>You can now log in using your credentials.</p>
            <a href="{$loginUrl}" style="display:inline-block;background:#1a3a5c;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;margin-top:8px;">Login to JSCA ERP</a>
          </div>
        </div>
        HTML;
    }
}
