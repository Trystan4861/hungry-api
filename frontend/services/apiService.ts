/**
 * Tipos de datos para la API
 */
interface LoginCredentials {
  email: string
  pass: string
  fingerid?: string
}

interface RegisterCredentials {
  email: string
  pass: string
}

interface RegisterResponse {
  result: boolean
  token?: string
  error_msg?: string
}

interface LoginResponse {
  result: boolean
  token?: string
  device?: {
    id: number
    fingerID: string
    is_master: number
  }
  error_msg?: string
}

interface ApiResponse<T = any> {
  result: boolean
  error_msg?: string
  data?: T
}

interface SyncData {
  categorias: any[]
  supermercados: any[]
  productos: any[]
}

interface SyncDataResponse {
  loginData?: {
    email: string
    token: string
    fingerID: string
    logged: boolean
  }
  categorias: any[]
  supermercados: any[]
  productos: any[]
}

interface QueueItem {
  id: string
  method: string
  url: string
  data: any
  timestamp: number
}

/**
 * Clase principal del servicio API
 * Maneja todas las comunicaciones con el backend de Hungry
 */
export class ApiService {
  private baseUrl: string
  private token: string | null = null
  private isOnline: boolean = navigator.onLine
  private processQueue: QueueItem[] = []
  private readonly QUEUE_STORAGE_KEY = 'hungry_api_queue'

  constructor(baseUrl: string = 'https://api.hungry.com') {
    this.baseUrl = baseUrl
    this.initializeQueue()
    this.setupOnlineListener()
  }

  /**
   * Inicializa la cola desde localStorage
   */
  private initializeQueue() {
    const savedQueue = localStorage.getItem(this.QUEUE_STORAGE_KEY)
    if (savedQueue) {
      this.processQueue = JSON.parse(savedQueue)
    }
    this.saveQueue()
  }

  /**
   * Configura los listeners para detectar cambios en la conexión
   */
  private setupOnlineListener() {
    window.addEventListener('online', () => {
      this.isOnline = true
      this.processQueuedItems()
    })
    window.addEventListener('offline', () => {
      this.isOnline = false
    })
  }

  /**
   * Guarda la cola en localStorage
   */
  private saveQueue() {
    localStorage.setItem(this.QUEUE_STORAGE_KEY, JSON.stringify(this.processQueue))
  }

  /**
   * Añade un item a la cola de procesos
   */
  private addToQueue(method: string, url: string, data: any) {
    const queueItem: QueueItem = {
      id: Math.random().toString(36).substring(7),
      method,
      url,
      data,
      timestamp: Date.now()
    }
    this.processQueue.push(queueItem)
    this.saveQueue()
  }

  /**
   * Procesa los items en cola cuando hay conexión
   */
  private async processQueuedItems() {
    if (!this.isOnline || this.processQueue.length === 0) return

    const items = [...this.processQueue]
    this.processQueue = []
    this.saveQueue()

    for (const item of items) {
      try {
        await fetch(`${this.baseUrl}${item.url}`, {
          method: item.method,
          headers: this.getHeaders(),
          body: JSON.stringify(item.data)
        })
      } catch (error) {
        // Si falla, devolvemos el item a la cola
        this.processQueue.push(item)
        this.saveQueue()
        break
      }
    }
  }

  /**
   * Wrapper para peticiones que gestiona el estado offline
   */
  private async makeRequest(method: string, url: string, data?: any): Promise<any> {
    if (!this.isOnline) {
      this.addToQueue(method, url, data)
      throw new Error('Dispositivo offline - Operación encolada')
    }

    try {
      const response = await fetch(`${this.baseUrl}${url}`, {
        method,
        headers: this.getHeaders(),
        body: data ? JSON.stringify(data) : undefined
      })
      return response.json()
    } catch (error) {
      if (!navigator.onLine) {
        this.isOnline = false
        this.addToQueue(method, url, data)
        throw new Error('Conexión perdida - Operación encolada')
      }
      throw error
    }
  }

  /**
   * Configura el token para las peticiones autenticadas
   * @param token Token JWT para autenticación
   */
  setToken(token: string) {
    this.token = token
  }

  /**
   * Obtiene los headers necesarios para las peticiones autenticadas
   * @returns Headers con el token de autenticación si existe
   */
  private getHeaders(): HeadersInit {
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    }
    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`
    }
    return headers
  }

  /**
   * Registra un nuevo usuario en el sistema
   * @param credentials Datos de registro (email y contraseña)
   * @returns Respuesta con el token si el registro es exitoso y hay verificación automática
   *          o mensaje de verificación si requiere validación por email
   */
  async register(credentials: RegisterCredentials): Promise<RegisterResponse> {
    return this.makeRequest('POST', '/register', credentials)
  }

  /**
   * Realiza el login del usuario
   * @param credentials Credenciales del usuario (email, pass, fingerid opcional)
   * @returns Respuesta con el token y datos del dispositivo si el login es exitoso
   */
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const data = await this.makeRequest('POST', '/login', credentials)
    if (data.result && data.token) {
      this.setToken(data.token)
    }
    return data
  }

  async getAllData(): Promise<ApiResponse<SyncDataResponse>> {
    return this.makeRequest('GET', '/getAll')
  }

  /**
   * Obtiene datos específicos sin sincronización automática
   * @param fingerid ID del dispositivo
   * @returns Datos del usuario incluyendo datos de login
   * @requires Autenticación
   */
  async getData(fingerid: string): Promise<ApiResponse<SyncDataResponse>> {
    return this.makeRequest('POST', '/getData', { fingerid })
  }

  /**
   * Sincroniza datos entre el cliente y el servidor
   * @param fingerid ID del dispositivo
   * @param data Datos a sincronizar (categorías, productos y supermercados)
   * @returns Datos actualizados del servidor
   * @requires Autenticación
   */
  async syncData(fingerid: string, data: SyncData): Promise<ApiResponse<SyncDataResponse>> {
    return this.makeRequest('POST', '/syncData', { fingerid, data })
  }

  /**
   * Obtiene las categorías del usuario
   * @returns Lista de categorías
   * @requires Autenticación
   */
  async getCategorias(): Promise<ApiResponse> {
    return this.makeRequest('GET', '/getCategorias')
  }

  /**
   * Actualiza el texto de una categoría
   * @param id_categoria ID de la categoría
   * @param text Nuevo texto para la categoría
   * @returns Resultado de la operación
   * @requires Autenticación
   */
  async updateCategoriaText(id_categoria: number, text: string): Promise<ApiResponse> {
    return this.makeRequest('POST', '/updateCategoriaText', { id_categoria, text })
  }

  /**
   * Actualiza la visibilidad de una categoría
   * @param id_categoria ID de la categoría
   * @param visible Estado de visibilidad (0=oculto, 1=visible)
   * @returns Resultado de la operación
   * @requires Autenticación
   */
  async updateCategoriaVisible(id_categoria: number, visible: number): Promise<ApiResponse> {
    return this.makeRequest('POST', '/updateCategoriaVisible', { id_categoria, visible })
  }

  /**
   * Crea un nuevo producto
   * @param data Datos del producto (categoría, supermercado, texto, cantidad opcional)
   * @returns ID del nuevo producto si se crea correctamente
   * @requires Autenticación
   */
  async newProducto(data: {
    id_categoria: number
    id_supermercado: number
    text: string
    amount?: number
  }): Promise<ApiResponse> {
    return this.makeRequest('POST', '/newProducto', data)
  }

  /**
   * Actualiza un producto existente
   * @param data Datos a actualizar del producto
   * @returns Resultado de la operación
   * @requires Autenticación
   */
  async updateProducto(data: {
    id_producto: number
    id_categoria?: number
    id_supermercado?: number
    text?: string
    amount?: number
    selected?: number
    done?: number
  }): Promise<ApiResponse> {
    return this.makeRequest('POST', '/updateProducto', data)
  }

  /**
   * Actualiza la cantidad de un producto
   * @param id_producto ID del producto
   * @param amount Nueva cantidad
   * @returns Resultado de la operación
   * @requires Autenticación
   */
  async updateProductoAmount(id_producto: number, amount: number): Promise<ApiResponse> {
    return this.makeRequest('POST', '/updateProductoAmount', { id_producto, amount })
  }

  /**
   * Obtiene el estado actual de la cola de procesos
   */
  getQueueStatus() {
    return {
      isOnline: this.isOnline,
      pendingItems: this.processQueue.length,
      queue: this.processQueue
    }
  }

  /**
   * Fuerza el procesamiento de la cola
   */
  async forceProcessQueue() {
    if (this.isOnline) {
      await this.processQueuedItems()
    }
  }
}

// Exportar una instancia por defecto
export default new ApiService()