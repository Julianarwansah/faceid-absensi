@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@push('styles')
    <style>
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #666;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .face-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .camera-container {
            position: relative;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
            max-width: 400px;
        }

        #video {
            width: 100%;
            display: block;
        }

        .status-text {
            text-align: center;
            padding: 10px;
            font-weight: 600;
        }

        .status-text.success {
            color: #10b981;
        }

        .status-text.error {
            color: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="form-card">
        <h3 style="margin-bottom: 20px;">Basic Information</h3>

        <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="employee_id">Employee ID *</label>
                    <input type="text" id="employee_id" name="employee_id"
                        value="{{ old('employee_id', $employee->employee_id) }}" required>
                    @error('employee_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $employee->full_name) }}"
                        required>
                    @error('full_name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}" required>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                </div>

                <div class="form-group">
                    <label for="department_id">Department *</label>
                    <select id="department_id" name="department_id" required>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" value="{{ old('position', $employee->position) }}">
                </div>

                <div class="form-group">
                    <label for="join_date">Join Date</label>
                    <input type="date" id="join_date" name="join_date"
                        value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label for="password">Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Employee
                </button>
            </div>
        </form>
    </div>

    <div class="form-card">
        <h3 style="margin-bottom: 15px;">Face Registration</h3>

        <div class="face-section">
            @if($employee->hasFaceRegistered())
                <div style="text-align: center; margin-bottom: 15px;">
                    <span style="color: #10b981; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Face Already Registered
                    </span>
                </div>
            @endif

            <div id="loading" style="text-align: center; padding: 20px;">
                <p>Click button below to register/update face</p>
            </div>

            <div id="camera-section" style="display: none;">
                <div class="camera-container">
                    <video id="video" autoplay muted playsinline></video>
                </div>
                <div id="status" class="status-text"></div>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                <button id="btn-start-camera" class="btn btn-primary" onclick="startCamera()">
                    <i class="fas fa-camera"></i> Start Camera
                </button>
                <button id="btn-register" class="btn btn-primary" style="display: none;" onclick="registerFace()">
                    <i class="fas fa-save"></i> Register Face
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const loading = document.getElementById('loading');
        const cameraSection = document.getElementById('camera-section');
        const btnStartCamera = document.getElementById('btn-start-camera');
        const btnRegister = document.getElementById('btn-register');
        const statusDiv = document.getElementById('status');

        let faceDescriptor = null;
        let modelsLoaded = false;

        async function loadModels() {
            if (modelsLoaded) return;

            try {
                await faceapi.nets.ssdMobilenetv1.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
                await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
                await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
                modelsLoaded = true;
            } catch (error) {
                console.error('Error loading models:', error);
                alert('Failed to load face recognition models');
            }
        }

        async function startCamera() {
            btnStartCamera.disabled = true;
            btnStartCamera.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

            await loadModels();

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' }
                });
                video.srcObject = stream;

                loading.style.display = 'none';
                cameraSection.style.display = 'block';
                btnStartCamera.style.display = 'none';
                btnRegister.style.display = 'inline-flex';

                statusDiv.innerHTML = 'Position your face in the camera';
                statusDiv.className = 'status-text';
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera');
                btnStartCamera.disabled = false;
                btnStartCamera.innerHTML = '<i class="fas fa-camera"></i> Start Camera';
            }
        }

        async function registerFace() {
            btnRegister.disabled = true;
            btnRegister.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting...';
            statusDiv.innerHTML = 'Detecting face...';
            statusDiv.className = 'status-text';

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.ssdMobilenetv1Options())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    statusDiv.innerHTML = 'No face detected. Please try again.';
                    statusDiv.className = 'status-text error';
                    btnRegister.disabled = false;
                    btnRegister.innerHTML = '<i class="fas fa-save"></i> Register Face';
                    return;
                }

                faceDescriptor = Array.from(detection.descriptor);

                // Capture photo
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const photo = canvas.toDataURL('image/png');

                // Send to server
                const response = await fetch('{{ route("face.register") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        employee_id: {{ $employee->id }},
                        face_descriptor: faceDescriptor,
                        photo: photo
                    })
                });

                const data = await response.json();

                if (data.success) {
                    statusDiv.innerHTML = '✓ Face registered successfully!';
                    statusDiv.className = 'status-text success';

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    statusDiv.innerHTML = '✗ Failed to register face';
                    statusDiv.className = 'status-text error';
                    btnRegister.disabled = false;
                    btnRegister.innerHTML = '<i class="fas fa-save"></i> Register Face';
                }
            } catch (error) {
                console.error('Error:', error);
                statusDiv.innerHTML = '✗ An error occurred';
                statusDiv.className = 'status-text error';
                btnRegister.disabled = false;
                btnRegister.innerHTML = '<i class="fas fa-save"></i> Register Face';
            }
        }
    </script>
@endpush
