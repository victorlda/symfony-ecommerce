/** Formato de erro da API (RFC 9457 + campo `code`). */
export type ProblemDetails = {
  type: string
  title: string
  status: number
  code: string
  detail: string
  errors?: { field: string; message: string }[]
}

export class ApiError extends Error {
  readonly problem: ProblemDetails

  constructor(problem: ProblemDetails) {
    super(problem.detail)
    this.name = 'ApiError'
    this.problem = problem
  }

  /** Agrupa os erros de validação por campo: { email: ['...'], password: ['...', '...'] } */
  fieldErrors(): Record<string, string[]> {
    const result: Record<string, string[]> = {}
    for (const { field, message } of this.problem.errors ?? []) {
      ;(result[field] ??= []).push(message)
    }
    return result
  }
}

function isProblem(value: unknown): value is ProblemDetails {
  return typeof value === 'object' && value !== null && 'code' in value && 'detail' in value
}

/** Devolve os dados de uma chamada da API ou lança um ApiError. */
export async function unwrap<T>(
  request: Promise<{ data?: T; error?: unknown; response: Response }>,
): Promise<T> {
  const { data, error, response } = await request

  if (!response.ok) {
    throw new ApiError(
      isProblem(error)
        ? error
        : {
            type: 'about:blank',
            title: 'Erro',
            status: response.status,
            code: 'unexpected_error',
            detail: 'Ocorreu um erro inesperado. Tente novamente.',
          },
    )
  }

  return data as T
}
