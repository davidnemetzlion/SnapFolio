<?php
/**
 * Minimal PHP Email Form handler
 * - Uses PHP mail() to send messages. For reliable SMTP delivery, install and configure a mailer (PHPMailer) or use a mail service.
 * - Returns the string "OK" on success (expected by the template), or an error message on failure.
 */

if (!class_exists('PHP_Email_Form')) {
  class PHP_Email_Form {
    public $to = '';
    public $from_name = '';
    public $from_email = '';
    public $subject = '';
    public $smtp = false; // optional array for future use
    public $ajax = false;

    private $messages = array();

    public function add_message($value, $label = '', $maxlen = 0) {
      $value = trim($value);
      if ($maxlen && is_numeric($maxlen) && strlen($value) > $maxlen) {
        $value = substr($value, 0, $maxlen);
      }
      $this->messages[] = array('label' => $label, 'value' => $value);
    }

    private function validate_email($email) {
      return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function send() {
      if (empty($this->to)) {
        return 'Recipient email address is not set.';
      }

      if (empty($this->from_email) || !$this->validate_email($this->from_email)) {
        return 'Invalid sender email address.';
      }

      // Build message body
      $body_lines = array();
      foreach ($this->messages as $m) {
        $label = $m['label'] ? $m['label'] . ':' : '';
        $body_lines[] = $label . ' ' . $m['value'];
      }
      $body = implode("\n", $body_lines);

      // Prepare headers
      $headers = 'From: ' . ($this->from_name ? $this->from_name . ' <' . $this->from_email . '>' : $this->from_email) . "\r\n";
      $headers .= 'Reply-To: ' . $this->from_email . "\r\n";
      $headers .= 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

      // Use mail(); for SMTP support you can integrate PHPMailer and check $this->smtp
      $success = false;
      try {
        // On some systems you may pass additional parameters like "-f" to set envelope sender.
        $success = @mail($this->to, $this->subject, $body, $headers);
      } catch (Exception $e) {
        return 'Error while sending email: ' . $e->getMessage();
      }

      if ($success) {
        return 'OK';
      } else {
        return 'Failed to send email. Check server mail configuration.';
      }
    }
  }
}

?>
