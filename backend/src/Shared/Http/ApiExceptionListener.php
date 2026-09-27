<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Converte todas as exceções das rotas /api para o formato Problem Details (RFC 9457).
 *
 * Roda depois do log de erros do Symfony (prioridade 0) e antes da renderização padrão (-128).
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -64)]
final readonly class ApiExceptionListener
{
    private const PROBLEMS = [
        400 => ['bad_request', 'Requisição inválida', 'A requisição não pôde ser processada. Verifique o formato dos dados enviados.'],
        401 => ['unauthenticated', 'Não autenticado', 'É necessário estar autenticado para acessar este recurso.'],
        403 => ['forbidden', 'Acesso negado', 'Você não tem permissão para realizar esta ação.'],
        404 => ['not_found', 'Não encontrado', 'O recurso solicitado não foi encontrado.'],
        405 => ['method_not_allowed', 'Método não permitido', 'Este método HTTP não é suportado neste endereço.'],
        409 => ['conflict', 'Conflito', 'A requisição conflita com o estado atual do recurso.'],
        415 => ['unsupported_media_type', 'Formato não suportado', 'Envie os dados no formato application/json.'],
        422 => ['validation_failed', 'Dados inválidos', 'Um ou mais campos são inválidos.'],
        429 => ['too_many_requests', 'Muitas requisições', 'Limite de requisições atingido. Tente novamente mais tarde.'],
        500 => ['internal_error', 'Erro interno', 'Ocorreu um erro inesperado. Tente novamente mais tarde.'],
    ];

    public function __construct(
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        // Em desenvolvimento, erros de servidor mantêm a página de debug do Symfony.
        if ($status >= 500 && $this->debug) {
            return;
        }

        [$code, $title, $detail] = self::PROBLEMS[$status] ?? self::PROBLEMS[$status >= 500 ? 500 : 400];
        $extensions = [];
        $previous = $exception->getPrevious();

        if ($previous instanceof ValidationFailedException) {
            $extensions['errors'] = $this->formatViolations($previous);
        } elseif ($previous instanceof \DomainException) {
            // Só mensagens das nossas regras de negócio chegam ao cliente.
            $detail = $previous->getMessage();
        }

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        $event->setResponse(ProblemResponse::create($status, $code, $title, $detail, $extensions, $headers));
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    private function formatViolations(ValidationFailedException $exception): array
    {
        $errors = [];

        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return $errors;
    }
}
