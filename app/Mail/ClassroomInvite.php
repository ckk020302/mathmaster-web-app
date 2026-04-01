<?php

namespace App\Mail;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomInvite extends Mailable
{
    use Queueable, SerializesModels;

    public Classroom $classroom;
    public User $teacher;

    public function __construct(Classroom $classroom, User $teacher)
    {
        $this->classroom = $classroom;
        $this->teacher = $teacher;
    }

    public function build(): self
    {
        return $this
            ->subject('Invitation to join: ' . $this->classroom->name)
            ->view('emails.classroom_invite');
    }
}

