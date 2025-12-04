@extends('layouts.app')

@section('title', 'Mark Attendance')
@section('page-title', 'Mark Attendance')

@push('styles')
    <style>
        .attendance-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .camera-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .camera-container {
            position: relative;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        #video {
            width: 100%;
            height: auto;
            display: block;
        }

        #canvas {
            position: absolute;
            top: 0;
            left: 0;
        }

        .camera-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            border: 3px solid #667eea;
            border-radius: 50%;
            pointer-events: none;
        }

        .status-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
        }

        .status-indicator.detecting {
            background: rgba(251, 191, 36, 0.9);
        }

        .status-indicator.matched {
            background: rgba(16, 185, 129, 0.9);
        }

        .status-indicator.no-match {
            background: rgba(239, 68, 68, 0.9);
        }

        .controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 30px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .info-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    <div class="attendance-container">
        @if(!$employee->hasFaceRegistered())
            <div class="camera-card">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 60px; color: #ef4444; margin-bottom: 20px;"></i>
                    <h2 style="margin-bottom: 15px;">Face Not Registered</h2>
                    <p style="color: #666; margin-bottom: 25px;">
                        You need to register your face before you can mark attendance. Please contact your administrator.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Employee Name:</span>
                    <span class="info-value">{{ $employee->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Employee ID:</span>
                    <span class="info-value">{{ $employee->employee_id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Today's Date:</span>
                    <span class="info-value">{{ \Carbon\Carbon::now()->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @if($todayAttendance)
                            @if($todayAttendance->check_in && $todayAttendance->check_out)
                                <span style="color: #10b981; font-weight: 600;">✓ Completed (In:
                                    {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') }}, Out:
                                    {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') }})</span>
                            @elseif($todayAttendance->check_in)
                                <span style="color: #f59e0b; font-weight: 600;">⏱ Checked In at
                                    {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') }}</span>
                            @endif
                        @else
                            <span style="color: #ef4444; font-weight: 600;">✗ Not Marked</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="camera-card">
                <div id="loading" class="loading">
                    <div class="spinner"></div>
                    <p>Loading face recognition models...</p>
                </div>

                <div id="camera-section" style="display: none;">
                    <div class="camera-container">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="canvas"></canvas>
                        <div class="camera-overlay"></div>
                        <div id="status-indicator" class="status-indicator">
                            <i class="fas fa-camera"></i> Position your face
                        </div>
                    </div>

                    <div class="controls">
                        @if(!$todayAttendance || !$todayAttendance->check_in)
                            <button id="btn-checkin" class="btn btn-success" disabled>
                                <i class="fas fa-sign-in-alt"></i> Check In
                            </button>
                        @endif

                        @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                            <button id="btn-checkout" class="btn btn-danger" disabled>
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        @endif

                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const statusIndicator = document.getElementById('status-indicator');
        const btnCheckin = document.getElementById('btn-checkin');
        const btnCheckout = document.getElementById('btn-checkout');
        const loading = document.getElementById('loading');
        const cameraSection = document.getElementById('camera-section');

        let faceDetected = false;
        let faceMatched = false;
        let storedDescriptor = null;
        let currentDescriptor = null;

        // Load face recognition models
        async function loadModels() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
                await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
                await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');

                loading.style.display = 'none';
                cameraSection.style.display = 'block';
                startVideo();
            } catch (error) {
                console.error('Error loading models:', error);
                loading.innerHTML = '<p style="color: #ef4444;">Failed to load face recognition models. Please refresh the page.</p>';
            }
        }

        // Start video stream
        async function startVideo() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    }
                });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    detectFace();
                };
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please grant camera permissions.');
            }
        }

        // Get stored face descriptor
        async function getStoredDescriptor() {
            try {
                const response = await fetch('{{ route("face.descriptor", $employee->id) }}');
                const data = await response.json();
                if (data.success) {
                    storedDescriptor = new Float32Array(data.descriptor);
                }
            } catch (error) {
                console.error('Error fetching stored descriptor:', error);
            }
        }

        // Detect face continuously
        async function detectFace() {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                faceDetected = true;
                currentDescriptor = detection.descriptor;

                // Draw detection box
                const box = detection.detection.box;
                ctx.strokeStyle = '#667eea';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);

                // Compare with stored descriptor
                if (storedDescriptor) {
                    const distance = faceapi.euclideanDistance(currentDescriptor, storedDescriptor);
                    faceMatched = distance < 0.35; // Threshold for matching

                    if (faceMatched) {
                        statusIndicator.className = 'status-indicator matched';
                        statusIndicator.innerHTML = `<i class="fas fa-check-circle"></i> Face Matched! (${confidence.toFixed(0)}% confidence)`;
                        if (btnCheckin) btnCheckin.disabled = false;
                        if (btnCheckout) btnCheckout.disabled = false;
                    } else {
                        statusIndicator.className = 'status-indicator no-match';
                        statusIndicator.innerHTML = `<i class="fas fa-times-circle"></i> Face Not Matched (${confidence.toFixed(0)}% - Need 65%+)`;
                        if (btnCheckin) btnCheckin.disabled = true;
                        if (btnCheckout) btnCheckout.disabled = true;
                    }
                }
            } else {
                faceDetected = false;
                faceMatched = false;
                statusIndicator.className = 'status-indicator detecting';
                statusIndicator.innerHTML = '<i class="fas fa-search"></i> Detecting face...';
                if (btnCheckin) btnCheckin.disabled = true;
                if (btnCheckout) btnCheckout.disabled = true;
            }

            requestAnimationFrame(detectFace);
        }

        // Capture photo
        function capturePhoto() {
            const captureCanvas = document.createElement('canvas');
            captureCanvas.width = video.videoWidth;
            captureCanvas.height = video.videoHeight;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            return captureCanvas.toDataURL('image/png');
        }

        // Check in
        if (btnCheckin) {
            btnCheckin.addEventListener('click', async function () {
                if (!faceMatched) {
                    alert('Face not matched. Please position your face properly.');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                try {
                    const photo = capturePhoto();
                    const response = await fetch('{{ route("attendance.checkin") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            face_match: faceMatched,
                            photo: photo,
                            latitude: null,
                            longitude: null
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✓ Check-in successful!');
                        window.location.reload();
                    } else {
                        alert('✗ ' + data.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-sign-in-alt"></i> Check In';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-sign-in-alt"></i> Check In';
                }
            });
        }

        // Check out
        if (btnCheckout) {
            btnCheckout.addEventListener('click', async function () {
                if (!faceMatched) {
                    alert('Face not matched. Please position your face properly.');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                try {
                    const photo = capturePhoto();
                    const response = await fetch('{{ route("attendance.checkout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            face_match: faceMatched,
                            photo: photo,
                            latitude: null,
                            longitude: null
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✓ Check-out successful!');
                        window.location.reload();
                    } else {
                        alert('✗ ' + data.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-sign-out-alt"></i> Check Out';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-sign-out-alt"></i> Check Out';
                }
            });
        }

        // Initialize
        loadModels();
        getStoredDescriptor();
    </script>
@endpush
