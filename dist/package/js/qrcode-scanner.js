/**
 * EXLIV Delivery - QR Code Scanner
 * Composant réutilisable pour scanner des QR codes dans toutes les sections
 */

(function() {
    'use strict';
    
    // Configuration du scanner QR
    const QRScanner = {
        scanner: null,
        isScanning: false,
        onScanCallback: null,
        
        // Initialiser le scanner
        init: function(containerId, onScanCallback) {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('Container QR scanner non trouvé:', containerId);
                return;
            }
            
            this.onScanCallback = onScanCallback;
            
            // Créer l'interface du scanner
            container.innerHTML = `
                <div class="qr-scanner-container">
                    <div id="qr-reader"></div>
                    <div class="qr-scanner-controls">
                        <button type="button" class="btn-start-scan" onclick="QRScanner.startScan()">
                            <i class="fa fa-camera"></i> Démarrer le scan
                        </button>
                        <button type="button" class="btn-stop-scan" onclick="QRScanner.stopScan()" style="display:none;">
                            <i class="fa fa-stop"></i> Arrêter le scan
                        </button>
                        <button type="button" class="btn-manual-input" onclick="QRScanner.showManualInput()">
                            <i class="fa fa-keyboard"></i> Saisie manuelle
                        </button>
                    </div>
                    <div class="qr-manual-input" style="display:none;">
                        <input type="text" id="qr-manual-input-field" placeholder="Entrez le code manuellement" />
                        <button type="button" onclick="QRScanner.submitManualInput()">Valider</button>
                        <button type="button" onclick="QRScanner.hideManualInput()">Annuler</button>
                    </div>
                </div>
            `;
            
            // Charger la bibliothèque HTML5-QRCode
            if (typeof Html5Qrcode === 'undefined') {
                // Charger le script si non chargé
                const script = document.createElement('script');
                script.src = 'js/html5-qrcode.min.js';
                script.onload = function() {
                    QRScanner.setupScanner();
                };
                document.head.appendChild(script);
            } else {
                this.setupScanner();
            }
        },
        
        // Configurer le scanner
        setupScanner: function() {
            try {
                this.scanner = new Html5Qrcode("qr-reader");
                console.log('Scanner QR initialisé');
            } catch (error) {
                console.error('Erreur lors de l\'initialisation du scanner QR:', error);
            }
        },
        
        // Démarrer le scan
        startScan: function() {
            if (!this.scanner) {
                this.setupScanner();
            }
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            this.scanner.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    // QR code détecté
                    this.handleScan(decodedText);
                },
                (errorMessage) => {
                    // Erreur de scan (ignorer)
                }
            ).then(() => {
                this.isScanning = true;
                document.querySelector('.btn-start-scan').style.display = 'none';
                document.querySelector('.btn-stop-scan').style.display = 'inline-block';
                console.log('Scan QR démarré');
            }).catch((err) => {
                console.error('Erreur lors du démarrage du scan:', err);
                alert('Impossible d\'accéder à la caméra. Vérifiez les permissions.');
            });
        },
        
        // Arrêter le scan
        stopScan: function() {
            if (this.scanner && this.isScanning) {
                this.scanner.stop().then(() => {
                    this.isScanning = false;
                    document.querySelector('.btn-start-scan').style.display = 'inline-block';
                    document.querySelector('.btn-stop-scan').style.display = 'none';
                    console.log('Scan QR arrêté');
                }).catch((err) => {
                    console.error('Erreur lors de l\'arrêt du scan:', err);
                });
            }
        },
        
        // Gérer le scan
        handleScan: function(decodedText) {
            console.log('QR code scanné:', decodedText);
            
            // Arrêter le scan temporairement
            this.stopScan();
            
            // Appeler le callback
            if (this.onScanCallback && typeof this.onScanCallback === 'function') {
                this.onScanCallback(decodedText);
            }
            
            // Optionnel: redémarrer le scan après un délai
            // setTimeout(() => this.startScan(), 2000);
        },
        
        // Afficher la saisie manuelle
        showManualInput: function() {
            document.querySelector('.qr-manual-input').style.display = 'block';
            document.getElementById('qr-manual-input-field').focus();
        },
        
        // Masquer la saisie manuelle
        hideManualInput: function() {
            document.querySelector('.qr-manual-input').style.display = 'none';
            document.getElementById('qr-manual-input-field').value = '';
        },
        
        // Soumettre la saisie manuelle
        submitManualInput: function() {
            const input = document.getElementById('qr-manual-input-field');
            const value = input.value.trim();
            
            if (value) {
                this.handleScan(value);
                this.hideManualInput();
            }
        }
    };
    
    // Exposer globalement
    window.QRScanner = QRScanner;
    
    // Styles CSS pour le scanner QR
    const style = document.createElement('style');
    style.textContent = `
        .qr-scanner-container {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        #qr-reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            min-height: 250px;
            background: #000;
            border-radius: 8px;
        }
        .qr-scanner-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .qr-scanner-controls button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-start-scan {
            background: #4CAF50;
            color: white;
        }
        .btn-start-scan:hover {
            background: #45a049;
        }
        .btn-stop-scan {
            background: #f44336;
            color: white;
        }
        .btn-stop-scan:hover {
            background: #da190b;
        }
        .btn-manual-input {
            background: #2196F3;
            color: white;
        }
        .btn-manual-input:hover {
            background: #0b7dda;
        }
        .qr-manual-input {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .qr-manual-input input {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            min-width: 200px;
        }
        .qr-manual-input input:focus {
            outline: none;
            border-color: #2196F3;
        }
        .qr-manual-input button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .qr-manual-input button:first-of-type {
            background: #4CAF50;
            color: white;
        }
        .qr-manual-input button:last-of-type {
            background: #f44336;
            color: white;
        }
    `;
    document.head.appendChild(style);
    
})();
