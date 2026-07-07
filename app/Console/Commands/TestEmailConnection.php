<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestConnectionMail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\DB;

#[Signature('weather:test-email {email}')]
#[Description('Sends a synchronous test email to verify SMTP and mail server settings.')]
class TestEmailConnection extends Command
{
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("=== Astronotify Mail Server Diagnostic ===");
        $this->info("Target Email: " . $email);
        $this->info("Mail default driver: " . config('mail.default'));
        
        $mailer = config('mail.default');
        if ($mailer === 'smtp') {
            $this->info("SMTP Host: " . config('mail.mailers.smtp.host'));
            $this->info("SMTP Port: " . config('mail.mailers.smtp.port'));
            $this->info("SMTP Username: " . config('mail.mailers.smtp.username'));
            $this->info("SMTP Encryption: " . config('mail.mailers.smtp.encryption', 'none'));
        }
        
        $this->info("From Address: " . config('mail.from.address'));
        $this->info("From Name: " . config('mail.from.name'));
        $this->info("Queue Connection: " . config('queue.default'));
        
        // Count jobs and failed jobs
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            $this->info("Pending Jobs in Queue: " . $pendingJobs);
            $this->info("Failed Jobs in Queue: " . $failedJobs);
        } catch (\Exception $e) {
            $this->warn("Could not retrieve queue jobs count: " . $e->getMessage());
        }
        
        $this->line("");
        $this->info("Attempting to send synchronous test email now...");
        
        try {
            // Send synchronously (ignoring queue driver) to verify mail server connection directly
            Mail::to($email)->send(new TestConnectionMail(now()->toDateTimeString()));
            $this->info("✅ Success! The test email has been sent successfully.");
            $this->info("Check your inbox (and spam folder) for 'Astronotify Mail Connection Test 🚀'.");
        } catch (\Exception $e) {
            $this->error("❌ Failure! An error occurred while sending the email:");
            $this->error("Error Message: " . $e->getMessage());
            $this->error("Exception Class: " . get_class($e));
            $this->line("");
            $this->error("Stack Trace:");
            $this->line($e->getTraceAsString());
            
            $this->line("");
            $this->warn("Tips for resolving SMTP issues:");
            $this->line("1. Verify your SMTP credentials in the .env file.");
            $this->line("2. Check if your hosting provider/server blocks outbound connections on the SMTP port (especially port 25 or 465/587).");
            $this->line("3. If using Gmail/Google Workspace, ensure you are using an 'App Password' instead of your main account password.");
        }
        
        $this->info("==========================================");
    }
}
