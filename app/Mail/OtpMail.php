<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;

    public function __construct($otp, $userName)
    {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Kode OTP Reset Password - Bumbu Opie')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'userName' => $this->userName,
            ]);
    }
}
