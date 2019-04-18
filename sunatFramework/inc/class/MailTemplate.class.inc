<?php declare(encoding = 'UTF-8');
if (!defined('MAIL_TEMPLATE_CLASS_INC')): define('MAIL_TEMPLATE_CLASS_INC', 1);

class MailTemplate extends TemplateBase {

	protected $last_mail_subject;
	protected $last_mail_body;

	public function __construct($name, $content = NULL) {
		parent::__construct($name, $content);
		$this->content = preg_replace('/\r\n|\r/s', "\n", $this->content);
		$this->content = preg_replace('/\n/s', "\r\n", $this->content);
		$this->last_mail_subject = NULL;
		$this->last_mail_body = NULL;
	}

	public function send(array $values, $to_name, $to_addr, $from_name, $from_addr, array $attachments = array()) {
		$headers = array();
		$options[] = array();

		$mail = $this->replace($values);
		list($subject, $body) = preg_split('/\n/s', $mail, 2);
		$this->last_mail_subject = $subject;
		$this->last_mail_body	 = $body;
		
		$body = mb_convert_encoding($body, 'ISO-2022-JP', mb_internal_encoding());

		$subject = mb_encode_mimeheader("Subject: $subject", 'ISO-2022-JP', 'B', "\n");
		$subject = preg_replace('/^Subject: /s', '', $subject);

		if (!is_null($to_name)) {
			$to = mb_encode_mimeheader("To: $to_name", 'ISO-2022-JP', 'B', "\n");
			$to = preg_replace('/^To: /s', '', $to)."\n <$to_addr>";
		}
		else {
			$to = "To: $to_addr";
			$to = preg_replace('/^To: /s', '', $to);
		}

		$headers[] = mb_encode_mimeheader("From: $from_name", 'ISO-2022-JP', 'B', "\n")."\n <$from_addr>";
		$headers[] = 'Mime-Version: 1.0';

		if ($attachments) {
			$finfo = new finfo(FILEINFO_MIME);

			$boundary = md5(uniqid(rand()));
			$headers[] = <<<MIME
Content-Type: multipart/mixed;
	boundary="$boundary"
Content-Transfer-Encoding: 7bit
MIME;

			$parts = array();
			$parts[] = "This is a multi-part message in MIME format.\r\n";
			$parts[] = <<<PART
--$boundary
Content-Type: text/plain; charset="ISO-2022-JP"
Content-Transfer-Encoding: 7bit

$body

PART;
			foreach ($attachments as $filename => $data) {
				$type = $finfo->buffer($data);
				$data = chunk_split(base64_encode($data));
				$filename = mb_encode_mimeheader($filename);
				$parts[] = <<<PART
--$boundary
Content-Type: $type;
Content-Transfer-Encoding: base64
Content-Disposition: attachment;
	filename="$filename"

$data
PART;
			}
			$parts[] = "--$boundary--";
			$body = implode("\r\n", $parts);
		}
		else {
			$headers[] = 'Content-Type: text/plain; charset="ISO-2022-JP"';
			$headers[] = 'Content-Transfer-Encoding: 7bit';
		}

		$body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
		return mail($to, $subject, $body, implode("\n", $headers), "-f$from_addr");
	}

	public function get_last_mail_data() {
		return array(
			'subject' => $this->last_mail_subject,
			'body'	  => $this->last_mail_body
		);
	}

};

endif/* MAIL_TEMPLATE_CLASS_INC */?>
