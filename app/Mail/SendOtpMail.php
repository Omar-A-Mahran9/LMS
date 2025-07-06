<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $student)
    {
        $this->otp = $otp;
        $this->student = $student;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $student=Auth::user();
        return $this->subject(__('رمز التحقق لتسجيل الدخول'))
                    ->view('emails.otp',compact(['student']));
    }
}
