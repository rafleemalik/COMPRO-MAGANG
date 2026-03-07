{{--
    USER PROFILE PAGE
    Profile information and password management for participants
    Using unified layout with glassmorphism design
--}}

@extends('layouts.dashboard-unified')

@section('title', 'Profile - PT Telkom Indonesia')

@php
    $role = match(auth()->user()->role) {
        'admin' => 'admin',
        'pembimbing' => 'mentor',
        default => 'participant',
    };
    $pageTitle = 'Profile';
@endphp

@push('styles')
<style>
/* ============================================
   PROFILE PAGE STYLES
   ============================================ */

/* ---- HERO (ph- prefix to avoid collision) ---- */
.ph-card {
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 2rem;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
}

.ph-cover {
    height: 140px;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    position: relative;
    overflow: hidden;
}
.ph-cover::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.04) 0%, transparent 45%);
}

.ph-body {
    padding: 0 2rem 1.75rem;
    position: relative;
}

/* Avatar */
.ph-avatar-wrap {
    position: relative;
    width: 130px;
    height: 130px;
    margin-top: -65px;
    margin-bottom: 1rem;
}

.ph-avatar {
    width: 130px !important;
    height: 130px !important;
    background: #f1f5f9 !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 5px solid #fff !important;
    box-shadow: 0 4px 24px rgba(0,0,0,0.14) !important;
    overflow: hidden !important;
    margin: 0 !important;
}
.ph-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block;
    border-radius: 50%;
}
.ph-avatar i {
    font-size: 3.5rem !important;
    color: #cbd5e1 !important;
}

/* Camera btn */
.ph-cam-btn {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 38px;
    height: 38px;
    background: #1e293b;
    border: 3px solid #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 2;
}
.ph-cam-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 14px rgba(30,41,59,0.45);
}
.ph-cam-btn i {
    font-size: 0.9rem;
    color: #fff;
}

/* Info */
.ph-info h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.4rem;
    line-height: 1.25;
}

.ph-meta {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}
.ph-meta span {
    font-size: 0.875rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.ph-meta span i {
    font-size: 0.8rem;
    color: #9ca3af;
    width: 16px;
    text-align: center;
}

.ph-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.ph-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
}
.ph-tag i {
    font-size: 0.72rem;
    color: #9ca3af;
}
.ph-tag.active {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}
.ph-tag.active i { color: #059669; }

/* Photo upload modal */
.photo-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.photo-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.photo-modal {
    background: white;
    border-radius: 20px;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s ease;
}

.photo-modal-overlay.active .photo-modal {
    transform: scale(1) translateY(0);
}

.photo-modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.photo-modal-header h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.photo-modal-header h4 i { color: #EE2E24; }

.photo-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.photo-modal-close:hover { background: #e5e7eb; color: #374151; }

.photo-modal-body {
    padding: 1.5rem;
}

.photo-dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 16px;
    padding: 2rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9fafb;
}

.photo-dropzone:hover,
.photo-dropzone.dragover {
    border-color: #EE2E24;
    background: rgba(238, 46, 36, 0.03);
}

.photo-dropzone-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(238, 46, 36, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #EE2E24;
    font-size: 1.25rem;
}

.photo-dropzone p {
    margin: 0 0 0.25rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #374151;
}

.photo-dropzone small {
    color: #9ca3af;
    font-size: 0.8rem;
}

.photo-preview-area {
    display: none;
    text-align: center;
}

.photo-preview-area.active {
    display: block;
}

.photo-preview-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
    margin: 0 auto 1rem;
    display: block;
}

.photo-preview-name {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.photo-modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.photo-modal-actions button {
    flex: 1;
    padding: 0.7rem 1rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: none;
}

.btn-photo-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-photo-cancel:hover { background: #e5e7eb; }

.btn-photo-save {
    background: linear-gradient(135deg, #EE2E24, #C41E1A);
    color: white;
    box-shadow: 0 2px 8px rgba(238, 46, 36, 0.25);
}

.btn-photo-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(238, 46, 36, 0.35);
}

.btn-photo-save:disabled {
    opacity: 0.5;
    pointer-events: none;
}

.btn-photo-remove {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    background: rgba(239, 68, 68, 0.08);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.15);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.75rem;
    width: 100%;
    justify-content: center;
}

.btn-photo-remove:hover {
    background: rgba(239, 68, 68, 0.15);
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Info Card - Enhanced Design */
.info-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #EE2E24, #C41E1A, #EE2E24);
    background-size: 200% 100%;
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.info-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.info-card-header {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, #fafbfc 0%, #ffffff 100%);
}

.info-card-header .header-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.15), rgba(196, 30, 26, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #EE2E24;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(238, 46, 36, 0.15);
    transition: all 0.3s ease;
}

.info-card:hover .info-card-header .header-icon {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 6px 16px rgba(238, 46, 36, 0.25);
}

.info-card-header h5 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.02em;
}

.info-card-body {
    padding: 1.5rem 1.75rem;
}

/* Info Items - Enhanced Design */
.info-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 14px;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #EE2E24, #C41E1A);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.info-item:hover {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border-color: rgba(238, 46, 36, 0.15);
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.info-item:hover::before {
    opacity: 1;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 180px;
    font-weight: 600;
    color: #475569;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.info-item-label i {
    color: #EE2E24;
    font-size: 1rem;
    width: 20px;
    text-align: center;
    background: rgba(238, 46, 36, 0.1);
    padding: 0.4rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-item-value {
    color: #1e293b;
    font-weight: 600;
    font-size: 0.95rem;
    flex: 1;
    line-height: 1.5;
}

/* Status Badge - Enhanced Design */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.status-badge:hover::before {
    left: 100%;
}

.status-badge.accepted {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.15));
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-badge.accepted:hover {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.2));
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.status-badge.rejected {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.15));
    color: #b91c1c;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-badge.rejected:hover {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(220, 38, 38, 0.2));
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.status-badge.finished {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.15));
    color: #1e40af;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-badge.finished:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(37, 99, 235, 0.2));
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.status-badge.pending {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.15));
    color: #b45309;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-badge.pending:hover {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.2));
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

/* Download Button - Enhanced */
.btn-download-letter {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.25rem;
    background: linear-gradient(135deg, #EE2E24, #C41E1A);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(238, 46, 36, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-download-letter::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-download-letter:hover::before {
    width: 300px;
    height: 300px;
}

.btn-download-letter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(238, 46, 36, 0.4);
    color: white;
}

.btn-download-letter i {
    font-size: 0.9rem;
    transition: transform 0.3s ease;
}

.btn-download-letter:hover i {
    transform: translateY(-2px);
}

/* Special styling for status item */
.info-item.status-item {
    background: linear-gradient(135deg, #fef3f2 0%, #fff7ed 100%);
    border: 2px solid rgba(238, 46, 36, 0.15);
    padding: 1.25rem 1.5rem;
}

.info-item.status-item:hover {
    background: linear-gradient(135deg, #fee2e2 0%, #ffedd5 100%);
    border-color: rgba(238, 46, 36, 0.25);
}

.info-item.status-item .info-item-label {
    font-size: 0.9rem;
    color: #7c2d12;
}

.info-item.status-item .info-item-value {
    font-size: 1rem;
}

/* Special styling for document items */
.info-item.document-item {
    background: linear-gradient(135deg, #f0f9ff 0%, #f8fafc 100%);
    border-color: rgba(59, 130, 246, 0.1);
}

.info-item.document-item:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #f1f5f9 100%);
    border-color: rgba(59, 130, 246, 0.2);
}

.info-item.document-item .info-item-label i {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}

/* Password Card */
.password-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.password-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.password-card-header .header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(238, 46, 36, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #EE2E24;
    flex-shrink: 0;
}

.password-card-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.password-card-body {
    padding: 1.5rem;
}

.password-card-body .form-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
}

.password-card-body .form-label i {
    color: #EE2E24;
    font-size: 0.85rem;
}

.password-card-body .form-control {
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 0.7rem 1rem;
    font-size: 0.9rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.password-card-body .form-control:focus {
    border-color: #EE2E24;
    box-shadow: 0 0 0 3px rgba(238, 46, 36, 0.1);
}

.btn-save-password {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.75rem;
    background: linear-gradient(135deg, #EE2E24, #C41E1A);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(238, 46, 36, 0.25);
}

.btn-save-password:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(238, 46, 36, 0.35);
    color: white;
}

/* Alert Styling */
.alert-modern {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.875rem;
}

.alert-modern.success {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.alert-modern.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.alert-modern i {
    margin-top: 0.1rem;
    font-size: 1rem;
}

.alert-modern ul {
    margin: 0;
    padding-left: 1.25rem;
}

.alert-modern .btn-close {
    margin-left: auto;
    padding: 0.5rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .ph-cover { height: 110px; }

    .ph-body {
        padding: 0 1.25rem 1.5rem;
        text-align: center;
    }

    .ph-avatar-wrap {
        width: 110px;
        height: 110px;
        margin: -55px auto 0.75rem;
    }
    .ph-avatar {
        width: 110px !important;
        height: 110px !important;
    }
    .ph-avatar i { font-size: 2.75rem !important; }

    .ph-info h2 { font-size: 1.25rem; }

    .ph-meta {
        justify-content: center;
        gap: 0.75rem;
    }

    .ph-tags {
        justify-content: center;
    }

    .info-item {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
    }

    .info-item-label {
        min-width: auto;
        width: 100%;
    }

    .info-item-value {
        width: 100%;
        text-align: left;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.45rem 1rem;
    }

    .btn-download-letter {
        width: 100%;
        justify-content: center;
        padding: 0.65rem 1rem;
    }

    .password-fields-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="ph-card">
    <div class="ph-cover"></div>
    <div class="ph-body">
        {{-- Avatar --}}
        <div class="ph-avatar-wrap">
            <div class="ph-avatar">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" id="heroAvatar">
                @else
                    <i class="fas fa-user" id="heroAvatarIcon"></i>
                @endif
            </div>
            <div class="ph-cam-btn" id="openPhotoModal" title="Ubah foto profil">
                <i class="fas fa-camera"></i>
            </div>
        </div>

        {{-- Info --}}
        <div class="ph-info">
            <h2>{{ $user->name ?? 'User' }}</h2>
            <div class="ph-meta">
                <span><i class="fas fa-envelope"></i> {{ $user->email ?? '-' }}</span>
                @if($user->phone)
                    <span><i class="fas fa-phone"></i> {{ $user->phone }}</span>
                @endif
                @if($role === 'participant' && $user->nim)
                    <span><i class="fas fa-id-badge"></i> {{ $user->nim }}</span>
                @endif
                @if($role === 'mentor' && $user->username)
                    <span><i class="fas fa-id-badge"></i> NIK: {{ $user->username }}</span>
                @endif
                @if($role === 'admin' && $user->username)
                    <span><i class="fas fa-id-badge"></i> {{ '@' . $user->username }}</span>
                @endif
            </div>
            <div class="ph-tags">
                @if($role === 'participant')
                    @if($user->university)
                        <span class="ph-tag"><i class="fas fa-university"></i> {{ $user->university }}</span>
                    @endif
                    @if($user->major)
                        <span class="ph-tag"><i class="fas fa-book"></i> {{ $user->major }}</span>
                    @endif
                    @if($application)
                        @if($application->status == 'accepted')
                            <span class="ph-tag active"><i class="fas fa-check-circle"></i> Magang Aktif</span>
                        @elseif($application->status == 'finished')
                            <span class="ph-tag active"><i class="fas fa-flag-checkered"></i> Magang Selesai</span>
                        @elseif($application->status == 'pending')
                            <span class="ph-tag" style="background:#fffbeb;border-color:#fde68a;color:#92400e;"><i class="fas fa-clock" style="color:#d97706;"></i> Menunggu Review</span>
                        @endif
                    @endif
                @elseif($role === 'mentor')
                    @if($divisionMentor && $divisionMentor->division)
                        <span class="ph-tag"><i class="fas fa-building"></i> {{ $divisionMentor->division->division_name }}</span>
                    @endif
                    <span class="ph-tag active"><i class="fas fa-user-tie"></i> Pembimbing</span>
                @elseif($role === 'admin')
                    <span class="ph-tag active" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;"><i class="fas fa-shield-alt" style="color:#2563eb;"></i> Administrator</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Photo Upload Modal --}}
<div class="photo-modal-overlay" id="photoModalOverlay">
    <div class="photo-modal">
        <div class="photo-modal-header">
            <h4><i class="fas fa-camera"></i> Ubah Foto Profil</h4>
            <button class="photo-modal-close" id="closePhotoModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="photo-modal-body">
            <div class="photo-dropzone" id="photoDropzone">
                <div class="photo-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <p>Klik atau seret foto ke sini</p>
                <small>JPG, JPEG, PNG — Maks. 2MB</small>
                <input type="file" id="photoFileInput" accept="image/jpeg,image/jpg,image/png" style="display: none;">
            </div>
            <div class="photo-preview-area" id="photoPreviewArea">
                <img src="" alt="Preview" class="photo-preview-img" id="photoPreviewImg">
                <div class="photo-preview-name" id="photoPreviewName"></div>
            </div>
            <div class="photo-modal-actions">
                <button class="btn-photo-cancel" id="btnPhotoCancel"><i class="fas fa-times"></i> Batal</button>
                <button class="btn-photo-save" id="btnPhotoSave" disabled><i class="fas fa-check"></i> Simpan</button>
            </div>
            @if($user->profile_picture)
            <button class="btn-photo-remove" id="btnPhotoRemove"><i class="fas fa-trash-alt"></i> Hapus Foto Profil</button>
            @endif
        </div>
    </div>
</div>

{{-- Info Cards Grid --}}
<div class="cards-grid">

    @if($role === 'participant')
    {{-- ============ PESERTA: Biodata ============ --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-user"></i></div>
            <h5>Biodata Peserta</h5>
        </div>
        <div class="info-card-body">
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-id-card"></i> <span>Nama</span></div>
                <div class="info-item-value">{{ $user->name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-envelope"></i> <span>Email</span></div>
                <div class="info-item-value">{{ $user->email ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-graduation-cap"></i> <span>NIM</span></div>
                <div class="info-item-value">{{ $user->nim ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-phone"></i> <span>No HP</span></div>
                <div class="info-item-value">{{ $user->phone ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-university"></i> <span>Universitas</span></div>
                <div class="info-item-value">{{ $user->university ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-book"></i> <span>Jurusan</span></div>
                <div class="info-item-value">{{ $user->major ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-id-badge"></i> <span>NIK (No. KTP)</span></div>
                <div class="info-item-value">{{ $user->ktp_number ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- PESERTA: Status Magang --}}
    @if($application)
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-clipboard-list"></i></div>
            <h5>Status Pengajuan Magang</h5>
        </div>
        <div class="info-card-body">
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-building"></i> <span>Divisi Penempatan</span></div>
                <div class="info-item-value">
                    {{ ($application->status == 'accepted' || $application->status == 'finished') ? ($application->divisionAdmin->division_name ?? '-') : '-' }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-user-tie"></i> <span>Mentor</span></div>
                <div class="info-item-value">
                    {{ ($application->status == 'accepted' || $application->status == 'finished') ? ($application->divisionMentor->mentor_name ?? '-') : '-' }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-tags"></i> <span>Bidang Peminatan</span></div>
                <div class="info-item-value">{{ $application->fieldOfInterest->name ?? '-' }}</div>
            </div>
            <div class="info-item status-item">
                <div class="info-item-label"><i class="fas fa-info-circle"></i> <span>Status</span></div>
                <div class="info-item-value">
                    @if($application->status == 'accepted')
                        <span class="status-badge accepted"><i class="fas fa-check-circle"></i> Diterima</span>
                    @elseif($application->status == 'rejected')
                        <span class="status-badge rejected"><i class="fas fa-times-circle"></i> Ditolak</span>
                    @elseif($application->status == 'finished')
                        <span class="status-badge finished"><i class="fas fa-flag-checkered"></i> Selesai</span>
                    @else
                        <span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-calendar"></i> <span>Periode Magang</span></div>
                <div class="info-item-value">
                    @if($application->start_date && $application->end_date)
                        @php
                            $start = \Carbon\Carbon::parse($application->start_date);
                            $end = \Carbon\Carbon::parse($application->end_date);
                            $totalDays = $start->diffInDays($end) + 1;
                        @endphp
                        {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }} ({{ $totalDays }} hari)
                    @else
                        -
                    @endif
                </div>
            </div>
            @if($application->status == 'accepted' || $application->status == 'finished')
            <div class="info-item document-item">
                <div class="info-item-label"><i class="fas fa-file-pdf"></i> <span>Surat Penerimaan</span></div>
                <div class="info-item-value">
                    @if($application->acceptance_letter_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->acceptance_letter_path))
                        <a href="{{ route('dashboard.acceptance-letter.download') }}" class="btn-download-letter" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                    @else
                        <span style="color: #9ca3af; font-style: italic;">Belum tersedia</span>
                    @endif
                </div>
            </div>
            <div class="info-item document-item">
                <div class="info-item-label"><i class="fas fa-map-marked-alt"></i> <span>Surat Izin Masuk Lokasi</span></div>
                <div class="info-item-value">
                    @if($application->location_permission_letter_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->location_permission_letter_path))
                        <a href="{{ asset('storage/' . $application->location_permission_letter_path) }}" class="btn-download-letter" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                    @else
                        <span style="color: #9ca3af; font-style: italic;">Belum tersedia</span>
                    @endif
                </div>
            </div>
            <div class="info-item document-item">
                <div class="info-item-label"><i class="fas fa-file-contract"></i> <span>Pakta Integritas</span></div>
                <div class="info-item-value">
                    @if($application->integrity_pact_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->integrity_pact_path))
                        <a href="{{ asset('storage/' . $application->integrity_pact_path) }}" class="btn-download-letter" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                    @else
                        <span style="color: #9ca3af; font-style: italic;">Belum tersedia</span>
                    @endif
                </div>
            </div>
            @endif
            @if($application->notes)
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-sticky-note"></i> <span>Catatan</span></div>
                <div class="info-item-value">{{ $application->notes }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @elseif($role === 'mentor')
    {{-- ============ MENTOR: Informasi Pembimbing ============ --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-user-tie"></i></div>
            <h5>Informasi Pembimbing</h5>
        </div>
        <div class="info-card-body">
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-id-card"></i> <span>Nama</span></div>
                <div class="info-item-value">{{ $user->name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-envelope"></i> <span>Email</span></div>
                <div class="info-item-value">{{ $user->email ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-id-badge"></i> <span>NIK</span></div>
                <div class="info-item-value">{{ $user->username ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-phone"></i> <span>No HP</span></div>
                <div class="info-item-value">{{ $user->phone ?? '-' }}</div>
            </div>
            @if($divisionMentor && $divisionMentor->division)
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-building"></i> <span>Divisi</span></div>
                <div class="info-item-value">{{ $divisionMentor->division->division_name }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-users"></i> <span>Jumlah Peserta Aktif</span></div>
                <div class="info-item-value">{{ $mentorParticipants->count() }} peserta</div>
            </div>
        </div>
    </div>

    {{-- MENTOR: Daftar Peserta Bimbingan --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-users"></i></div>
            <h5>Peserta Bimbingan</h5>
        </div>
        <div class="info-card-body">
            @if($mentorParticipants->isNotEmpty())
                @foreach($mentorParticipants as $participant)
                <div class="info-item">
                    <div class="info-item-label"><i class="fas fa-user-graduate"></i> <span>{{ $participant->user->nim ?? '-' }}</span></div>
                    <div class="info-item-value">{{ $participant->user->name }}</div>
                </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 1.5rem; color: #9ca3af;">
                    <i class="fas fa-inbox" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                    Belum ada peserta bimbingan
                </div>
            @endif
        </div>
    </div>

    @elseif($role === 'admin')
    {{-- ============ ADMIN: Informasi Admin ============ --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-shield-alt"></i></div>
            <h5>Informasi Administrator</h5>
        </div>
        <div class="info-card-body">
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-id-card"></i> <span>Nama</span></div>
                <div class="info-item-value">{{ $user->name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-envelope"></i> <span>Email</span></div>
                <div class="info-item-value">{{ $user->email ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-at"></i> <span>Username</span></div>
                <div class="info-item-value">{{ $user->username ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-phone"></i> <span>No HP</span></div>
                <div class="info-item-value">{{ $user->phone ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-user-shield"></i> <span>Role</span></div>
                <div class="info-item-value">
                    <span class="status-badge accepted"><i class="fas fa-shield-alt"></i> Administrator</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ADMIN: Ringkasan Sistem --}}
    @if($adminStats)
    <div class="info-card">
        <div class="info-card-header">
            <div class="header-icon"><i class="fas fa-chart-bar"></i></div>
            <h5>Ringkasan Sistem</h5>
        </div>
        <div class="info-card-body">
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-user-check"></i> <span>Peserta Aktif</span></div>
                <div class="info-item-value">{{ $adminStats['total_participants'] }} peserta</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-user-tie"></i> <span>Total Pembimbing</span></div>
                <div class="info-item-value">{{ $adminStats['total_mentors'] }} pembimbing</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-building"></i> <span>Divisi Aktif</span></div>
                <div class="info-item-value">{{ $adminStats['total_divisions'] }} divisi</div>
            </div>
            <div class="info-item">
                <div class="info-item-label"><i class="fas fa-clock"></i> <span>Pengajuan Pending</span></div>
                <div class="info-item-value">
                    @if($adminStats['pending_applications'] > 0)
                        <span style="color: #d97706; font-weight: 600;">{{ $adminStats['pending_applications'] }} menunggu review</span>
                    @else
                        <span style="color: #059669;">Tidak ada</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @endif
</div>

{{-- Mentor: Edit Biodata Kontak --}}
@if($role === 'mentor')
<div class="info-card" style="margin-bottom: 1.5rem;">
    <div class="info-card-header">
        <div class="header-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563EB;">
            <i class="fas fa-edit"></i>
        </div>
        <h5>Edit Biodata Kontak</h5>
    </div>
    <div class="info-card-body">
        @if(session('biodata_success'))
            <div class="alert-modern success" style="margin-bottom: 1rem;">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('biodata_success') }}</span>
            </div>
        @endif
        @if($errors->biodata->any())
            <div class="alert-modern danger" style="margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <ul>
                        @foreach($errors->biodata->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1rem;">
            Data kontak ini akan ditampilkan kepada peserta magang yang Anda bimbing.
        </p>
        <form method="POST" action="{{ route('dashboard.profile.biodata') }}">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;" class="password-fields-grid">
                <div>
                    <label for="mentor_phone" class="form-label" style="font-weight: 600; color: #374151; font-size: 0.875rem; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-phone" style="color: #EE2E24; font-size: 0.85rem;"></i> No. Telepon
                    </label>
                    <input type="text" class="form-control" id="mentor_phone" name="phone"
                           value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                           style="border: 1px solid #d1d5db; border-radius: 12px; padding: 0.7rem 1rem; font-size: 0.9rem;">
                </div>
                <div>
                    <label for="mentor_email" class="form-label" style="font-weight: 600; color: #374151; font-size: 0.875rem; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope" style="color: #EE2E24; font-size: 0.85rem;"></i> Email
                    </label>
                    <input type="email" class="form-control" id="mentor_email" name="email"
                           value="{{ old('email', $user->email) }}" placeholder="email@contoh.com" required
                           style="border: 1px solid #d1d5db; border-radius: 12px; padding: 0.7rem 1rem; font-size: 0.9rem;">
                </div>
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn-save-password">
                    <i class="fas fa-save"></i> Simpan Biodata
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Password Card --}}
<div class="password-card">
    <div class="password-card-header">
        <div class="header-icon">
            <i class="fas fa-key"></i>
        </div>
        <h5>Ganti Password</h5>
    </div>
    <div class="password-card-body">
        @if(session('success'))
            <div class="alert-modern success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-modern danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;" class="password-fields-grid">
                <div>
                    <label for="current_password" class="form-label">
                        <i class="fas fa-lock"></i> Password Saat Ini
                    </label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                           id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password" class="form-label">
                        <i class="fas fa-key"></i> Password Baru
                    </label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-check-double"></i> Konfirmasi Password
                    </label>
                    <input type="password" class="form-control"
                           id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>
            <div style="text-align: right; margin-top: 1.5rem;">
                <button type="submit" class="btn-save-password">
                    <i class="fas fa-save"></i> Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('photoModalOverlay');
    const openBtn = document.getElementById('openPhotoModal');
    const closeBtn = document.getElementById('closePhotoModal');
    const dropzone = document.getElementById('photoDropzone');
    const fileInput = document.getElementById('photoFileInput');
    const previewArea = document.getElementById('photoPreviewArea');
    const previewImg = document.getElementById('photoPreviewImg');
    const previewName = document.getElementById('photoPreviewName');
    const btnCancel = document.getElementById('btnPhotoCancel');
    const btnSave = document.getElementById('btnPhotoSave');
    const btnRemove = document.getElementById('btnPhotoRemove');

    let selectedFile = null;

    function openModal() {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        resetPreview();
    }

    function resetPreview() {
        selectedFile = null;
        dropzone.style.display = '';
        previewArea.classList.remove('active');
        btnSave.disabled = true;
        fileInput.value = '';
    }

    function showPreview(file) {
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
            dropzone.style.display = 'none';
            previewArea.classList.add('active');
            btnSave.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    function validateFile(file) {
        const allowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) {
            alert('Format file harus JPG, JPEG, atau PNG.');
            return false;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB.');
            return false;
        }
        return true;
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });

    dropzone.addEventListener('click', function() { fileInput.click(); });

    fileInput.addEventListener('change', function() {
        if (this.files[0] && validateFile(this.files[0])) {
            showPreview(this.files[0]);
        }
    });

    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    dropzone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files[0] && validateFile(e.dataTransfer.files[0])) {
            showPreview(e.dataTransfer.files[0]);
        }
    });

    btnCancel.addEventListener('click', function() {
        if (selectedFile) {
            resetPreview();
        } else {
            closeModal();
        }
    });

    btnSave.addEventListener('click', function() {
        if (!selectedFile) return;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah...';

        const formData = new FormData();
        formData.append('profile_picture', selectedFile);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("dashboard.pre-acceptance.profile-picture") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Update hero avatar
                const avatar = document.querySelector('.ph-avatar');
                const existingImg = avatar.querySelector('img');
                const existingIcon = avatar.querySelector('i');
                if (existingIcon) existingIcon.remove();
                if (existingImg) {
                    existingImg.src = data.path;
                } else {
                    const img = document.createElement('img');
                    img.src = data.path;
                    img.alt = 'Profile';
                    avatar.appendChild(img);
                }
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Gagal mengunggah foto.');
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-check"></i> Simpan';
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan. Coba lagi.');
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-check"></i> Simpan';
        });
    });

    if (btnRemove) {
        btnRemove.addEventListener('click', function() {
            if (!confirm('Hapus foto profil?')) return;
            btnRemove.disabled = true;
            btnRemove.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';

            fetch('{{ route("dashboard.pre-acceptance.profile-picture.remove") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus foto.');
                    btnRemove.disabled = false;
                    btnRemove.innerHTML = '<i class="fas fa-trash-alt"></i> Hapus Foto Profil';
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan.');
                btnRemove.disabled = false;
                btnRemove.innerHTML = '<i class="fas fa-trash-alt"></i> Hapus Foto Profil';
            });
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) closeModal();
    });
});
</script>
@endpush
