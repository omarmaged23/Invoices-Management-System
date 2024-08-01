<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Add_User extends Notification
{
    use Queueable;
    private $line1;
    private $line2;
    /**
     * Create a new notification instance.
     */
    public function __construct($user_status)
    {
        if($user_status == 'مفعل')
        {
            $this->line1 = 'يمكنك الان تصفح برنامج الفواتير بهذا البريد الاليكترونى';
            $this->line2 = 'لمعرفة كلمة المرور او تعديلها برجاء التواصل مع وحدة تكنولوجيا المعلومات الخاصة بك';
        }
        else{
            $this->line1 = 'تم انشاء حساب جديد لكن حالة المستخدم غير مفعل';
            $this->line2 = 'لمزيد من التفاصيل برجاء التواصل مع وحدة تكنولوجيا المعلومات الخاصة بك';
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = 'http://127.0.0.1:8000/';

        return (new MailMessage)
            ->greeting('مرحبا')
            ->subject('مستخدم جديد')
            ->line($this->line1)
            ->line($this->line2)
            ->action('التوجه للبرنامج', $url)
            ->line('شكرا لاستخدامك برنامج الفواتير')
            ->salutation('تحياتى');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
