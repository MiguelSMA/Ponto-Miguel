/**
 * Módulo de Captura de Câmera para Sistema Antifraude REP
 */

export class CameraModule {
  constructor() {
    this.stream = null;
  }

  /**
   * Verifica se a API de mídia/câmera é suportada pelo navegador.
   * @returns {boolean}
   */
  isSupported() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
  }

  /**
   * Inicializa o fluxo de vídeo da câmera e conecta ao elemento HTML <video>.
   * @param {HTMLVideoElement} videoElement - Elemento <video> onde a câmera será exibida.
   * @param {Object} options - Opções adicionais de restrição (ex: width, height, facingMode).
   * @returns {Promise<MediaStream>}
   */
  async startCamera(videoElement, options = {}) {
    if (!this.isSupported()) {
      throw new Error('A API de câmera (getUserMedia) não é suportada por este navegador.');
    }

    // Se já houver um fluxo ativo, interrompe antes de iniciar um novo
    this.stopCamera();

    const constraints = {
      video: {
        width: { ideal: options.width || 640 },
        height: { ideal: options.height || 480 },
        facingMode: options.facingMode || 'user'
      },
      audio: false
    };

    try {
      this.stream = await navigator.mediaDevices.getUserMedia(constraints);
      if (videoElement) {
        videoElement.srcObject = this.stream;
        await videoElement.play();
      }
      return this.stream;
    } catch (error) {
      console.error('Erro ao acessar a câmera:', error);
      throw new Error(`Não foi possível acessar a câmera: ${error.message}`);
    }
  }

  /**
   * Captura uma imagem (snapshot) do vídeo atual e retorna no formato Base64 (Data URL).
   * @param {HTMLVideoElement} videoElement - Elemento <video> com fluxo ativo.
   * @param {string} mimeType - Formato da imagem ('image/jpeg', 'image/png', etc).
   * @param {number} quality - Qualidade da imagem (0 a 1).
   * @returns {string} Data URL em formato Base64.
   */
  takeSnapshot(videoElement, mimeType = 'image/jpeg', quality = 0.85) {
    if (!videoElement || videoElement.readyState < 2) {
      throw new Error('O elemento de vídeo não está pronto para captura.');
    }

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.videoWidth || 640;
    canvas.height = videoElement.videoHeight || 480;

    const ctx = canvas.getContext('2d');

    // Inverter horizontalmente se a câmera for frontal ('user') para efeito espelho visual correto se necessário
    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL(mimeType, quality);
  }

  /**
   * Interrompe todas as faixas (tracks) do fluxo da câmera e libera o dispositivo.
   */
  stopCamera() {
    if (this.stream) {
      this.stream.getTracks().forEach(track => track.stop());
      this.stream = null;
    }
  }
}

// Instância padrão única para fácil reutilização
export const camera = new CameraModule();
