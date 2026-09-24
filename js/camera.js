/**
 * Módulo de Câmera Antifraude para Registro de Ponto (REP)
 */

export class CameraModule {
    constructor(videoElement, canvasElement = null) {
        this.videoElement = videoElement;
        this.canvasElement = canvasElement || document.createElement('canvas');
        this.stream = null;
        this.isReady = false;
        this.lastError = null;
    }

    /**
     * Inicializa a câmera e exibe o preview contínuo no elemento <video>
     */
    async init() {
        this.isReady = false;
        this.lastError = null;

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.lastError = 'Seu navegador ou ambiente não suporta acesso à câmera via HTTPS/localhost.';
            throw new Error(this.lastError);
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            this.videoElement.srcObject = this.stream;
            await this.videoElement.play();
            this.isReady = true;
            return true;
        } catch (err) {
            console.error('[REP Camera] Erro ao acessar a câmera:', err);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                this.lastError = 'Permissão de acesso à câmera foi negada. A foto é obrigatória para o ponto.';
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                this.lastError = 'Nenhuma câmera foi encontrada no dispositivo.';
            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                this.lastError = 'A câmera já está sendo usada por outro aplicativo ou processo.';
            } else {
                this.lastError = `Erro na câmera: ${err.message || 'Não foi possível iniciar a captura.'}`;
            }
            this.isReady = false;
            throw new Error(this.lastError);
        }
    }

    /**
     * Captura um frame estático do elemento <video> e converte para Base64 (JPEG)
     * @returns {string} String DataURL em formato Base64
     */
    captureSnapshot() {
        if (!this.isReady || !this.videoElement) {
            throw new Error('A câmera não está pronta para captura da foto.');
        }

        const width = this.videoElement.videoWidth || 640;
        const height = this.videoElement.videoHeight || 480;

        this.canvasElement.width = width;
        this.canvasElement.height = height;

        const ctx = this.canvasElement.getContext('2d');
        // Inverte horizontalmente caso o vídeo esteja espelhado
        ctx.save();
        ctx.drawImage(this.videoElement, 0, 0, width, height);
        ctx.restore();

        const base64Image = this.canvasElement.toDataURL('image/jpeg', 0.85);
        if (!base64Image || base64Image === 'data:,') {
            throw new Error('Falha ao gerar o frame da imagem da câmera.');
        }

        return base64Image;
    }

    /**
     * Interrompe o fluxo de vídeo da câmera
     */
    stop() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }
        this.isReady = false;
    }
}
