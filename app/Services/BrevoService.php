<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Qlib\Qlib;

/**
 * Serviço para envio de e-mails transacionais via Brevo.
 * Requer configuração das variáveis de ambiente:
 * - BREVO_API_KEY
 * - BREVO_SENDER_EMAIL
 * - BREVO_SENDER_NAME (opcional)
 */
class BrevoService
{
    /**
     * Retorna a URL base da API do Brevo.
     * @return string
     */
    protected static function baseUrl(): string
    {
        return (string) (config('services.brevo.api_url') ?: 'https://api.brevo.com/v3');
    }
    /**
     * Verifica se a integração com Brevo está configurada.
     * @return bool Verdadeiro quando existe API key e e-mail do remetente.
     */
    public static function isConfigured(): bool
    {
        $apiKey = config('services.brevo.api_key') ?: env('BREVO_API_KEY');
        $sender = self::senderEmail();
        return !empty($apiKey) && !empty($sender);
    }

    /**
     * Retorna o e-mail do remetente a ser utilizado.
     * @return string
     */
    protected static function senderEmail(): string
    {
        $email = config('services.brevo.sender.email');
        if (!empty($email)) {
            return (string) $email;
        }
        $fallback = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS');
        return (string) ($fallback ?: '');
    }

    /**
     * Retorna o nome do remetente a ser utilizado.
     * @return string
     */
    protected static function senderName(): string
    {
        $name = config('services.brevo.sender.name');
        if (!empty($name)) {
            return (string) $name;
        }
        return (string) (config('mail.from.name') ?: 'CRM Aeroclube');
    }

    /**
     * Envia um e-mail transacional via Brevo.
     * @param array $tos Lista de destinatários no formato [['email' => 'a@b.com'], ...]
     * @param string $subject Assunto do e-mail
     * @param string $textContent Conteúdo de texto simples do e-mail
     * @param array $options Opções adicionais (ex.: ['tags' => ['tag1','tag2']])
     * @return array Retorno com 'exec' (bool), 'response' e/ou 'error'
     */
    public static function sendEmail(array $tos, string $subject, string $textContent, array $options = []): array
    {
        $ret = ['exec' => false];
        if (!self::isConfigured()) {
            $ret['mens'] = 'Brevo não configurado';
            Log::warning('BrevoService: configuração ausente (BREVO_API_KEY/BREVO_SENDER_EMAIL)');
            return $ret;
        }
        $payload = [
            'sender' => [
                'name' => self::senderName(),
                'email' => self::senderEmail(),
            ],
            'to' => $tos,
            'subject' => $subject,
            'textContent' => $textContent,
        ];
        // Suporte a HTML e templates do Brevo
        if (!empty($options['html'])) {
            $payload['htmlContent'] = (string) $options['html'];
        }
        if (!empty($options['template_id'])) {
            $payload['templateId'] = (int) $options['template_id'];
            if (!empty($options['params']) && is_array($options['params'])) {
                $payload['params'] = $options['params'];
            }
        }
        if (!empty($options['tags']) && is_array($options['tags'])) {
            $payload['tags'] = $options['tags'];
        }
        try {
            $resp = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => (string) config('services.brevo.api_key'),
            ])->post(self::baseUrl().'/smtp/email', $payload);
            $ret['response'] = Qlib::lib_json_array($resp, true);
            $ret['exec'] = $resp->successful();
        } catch (\Throwable $e) {
            $ret['error'] = $e->getMessage();
            Log::error('BrevoService: erro ao enviar e-mail', ['error' => $e->getMessage()]);
        }
        return $ret;
    }

    /**
     * Notifica a conclusão de assinatura a uma lista de e-mails.
     * @param array $emails Lista simples de e-mails ['a@b.com','c@d.com']
     * @param array $data Dados do webhook para compor mensagem (ex.: ['name' => 'Contrato'])
     * @return array Retorno da operação
     */
    public static function notifySignatureCompleted(array $emails, array $data): array
    {
        $tos = array_map(fn ($e) => ['email' => $e], $emails);
        $docName = isset($data['name']) ? (string) $data['name'] : '';
        $externalId = isset($data['external_id']) ? (string) $data['external_id'] : '';
        $verifyUrl = '';
        if (isset($data['signers']) && is_array($data['signers'])) {
            $preferred = null;
            if (isset($data['signers'][1]['sign_url']) && !empty($data['signers'][1]['sign_url'])) {
                $preferred = (string) $data['signers'][1]['sign_url'];
            } elseif (isset($data['signers'][0]['sign_url']) && !empty($data['signers'][0]['sign_url'])) {
                $preferred = (string) $data['signers'][0]['sign_url'];
            }
            if (!empty($preferred)) {
                $preferred = trim($preferred);
                $preferred = trim($preferred, " `\"'");
                $verifyUrl = $preferred;
            }
        }
        $subject = 'Assinatura concluída';
        $text = "A assinatura do documento foi concluída.\n";
        if ($docName) {
            $text .= "Documento: {$docName}\n";
        }
        if ($externalId) {
            $text .= "Token: {$externalId}\n";
        }
        if ($verifyUrl) {
            $text .= "Conferência: {$verifyUrl}\n";
        }
        // HTML com moldura e logo
        $safeDoc = htmlspecialchars((string) $docName, ENT_QUOTES, 'UTF-8');
        $safeTok = htmlspecialchars((string) $externalId, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars((string) $verifyUrl, ENT_QUOTES, 'UTF-8');
        $brandLogo = isset($data['brand_logo']) ? trim((string) $data['brand_logo'], " `\"'") : '';
        $safeLogo = htmlspecialchars((string) $brandLogo, ENT_QUOTES, 'UTF-8');
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Assinatura concluída</title></head><body style="margin:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;">
        <div style="max-width:620px;margin:24px auto;padding:0 12px;">
          <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="background:#073b5b;padding:16px;text-align:center;color:#fff;">
              '.($safeLogo ? '<img alt="Logo" src="'.$safeLogo.'" style="max-height:42px;vertical-align:middle">' : '<strong>CRM Aeroclube</strong>').'
            </div>
            <div style="padding:20px;color:#111827;">
              <h2 style="margin:0 0 12px 0;font-size:18px;color:#073b5b;">Assinatura concluída</h2>
              '.($safeDoc ? '<p style="margin:0 0 8px 0;"><strong>Documento:</strong> '.$safeDoc.'</p>' : '').'
              '.($safeTok ? '<p style="margin:0 0 8px 0;"><strong>Token:</strong> '.$safeTok.'</p>' : '').'
              '.($safeUrl ? '<p style="margin:0 0 8px 0;"><strong>Conferência:</strong> <a href="'.$safeUrl.'" style="color:#0ea5e9;text-decoration:none" target="_blank">'.$safeUrl.'</a></p>' : '').'
              <p style="margin:16px 0 0 0;color:#6b7280;font-size:12px;">Este e-mail foi gerado automaticamente pelo CRM.</p>
            </div>
          </div>
        </div>
        </body></html>';
        return self::sendEmail($tos, $subject, $text, ['tags' => ['zapsing', 'webhook', 'signed'], 'html' => $html]);
    }
}
