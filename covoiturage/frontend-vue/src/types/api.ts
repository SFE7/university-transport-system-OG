export interface ApiResponse<T> {
  data: T
  message: string
  status: number
  errors?: Record<string, string[]>
}

export interface PaginatedData<T> {
  data: T[]
  meta: {
    total: number
    page: number
    per_page: number
    last_page: number
  }
}
