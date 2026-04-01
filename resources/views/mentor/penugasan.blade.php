{{--
    MENTOR PENUGASAN & PENILAIAN PAGE
    Assignment and grading management
    Using unified layout with glassmorphism design
--}}

@extends('layouts.dashboard-unified')

@section('title', 'Penugasan & Penilaian')

@php
    $role = 'mentor';
    $pageTitle = 'Penugasan & Penilaian';
    $pageSubtitle = 'Kelola tugas dan penilaian peserta magang';

    // Calculate statistics
    $totalParticipants = $participants->count();
    $activeParticipants = $participants->filter(function($p) {
        if ($p->start_date) {
            return \Carbon\Carbon::parse($p->start_date)->lte(now());
        }
        return true;
    })->count();

    $totalTasks = 0;
    $pendingGrading = 0;
    $completedTasks = 0;

    foreach ($participants as $participant) {
        $assignments = $participant->user->assignments ?? collect();
        $totalTasks += $assignments->count();
        $completedTasks += $assignments->whereNotNull('grade')->count();

        foreach ($assignments as $assignment) {
            $hasSubmissions = $assignment->submissions && $assignment->submissions->count() > 0;
            if (($hasSubmissions || $assignment->submission_file_path) && is_null($assignment->grade) && (int) $assignment->is_revision !== 1) {
                $pendingGrading++;
            }
        }
    }
@endphp

@push('styles')
<style>
/* ============================================
   PENUGASAN PAGE STYLES
   ============================================ */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

/* Hero Section */
.mentor-hero {
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 50%, #9B1B1B 100%);
    border-radius: 24px;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    color: white;
}

.mentor-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(ellipse, rgba(255,255,255,0.15) 0%, transparent 70%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.hero-text h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.hero-text p {
    font-size: 1rem;
    opacity: 0.9;
    max-width: 500px;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-icon.purple {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    color: white;
}

.stat-icon.green {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
}

.stat-icon.yellow {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    color: white;
}

.stat-icon.red {
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 100%);
    color: white;
}

.stat-info h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-info p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Tabs Navigation */
.tabs-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    padding: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tab-btn {
    flex: 1;
    min-width: 150px;
    padding: 0.875rem 1.5rem;
    border: none;
    background: transparent;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.9375rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.tab-btn:hover {
    background: rgba(238, 46, 36, 0.05);
    color: #1f2937;
}

.tab-btn.active {
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(238, 46, 36, 0.3);
}

.tab-btn .badge-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.125rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
}

.tab-btn.active .badge-count {
    background: rgba(255, 255, 255, 0.25);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Participants Grid */
.participants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

.participant-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.5rem;
    border: 2px solid transparent;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.participant-card:hover {
    border-color: #EE2E24;
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(238, 46, 36, 0.15);
}

.participant-card.not-started {
    opacity: 0.6;
    background: rgba(249, 250, 251, 0.95);
    cursor: not-allowed;
}

.participant-card.not-started:hover {
    border-color: transparent;
    transform: none;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}

.participant-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.participant-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    flex-shrink: 0;
    overflow: hidden;
}

.participant-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.participant-info {
    flex: 1;
    min-width: 0;
}

.participant-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 1rem;
    margin: 0 0 0.25rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.participant-nim {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.participant-start-date {
    font-size: 0.75rem;
    color: #F59E0B;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.badge-not-started {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #FEF3C7;
    color: #92400E;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-weight: 500;
}

.participant-stats-row {
    display: flex;
    gap: 1rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.participant-stat {
    flex: 1;
    text-align: center;
}

.participant-stat-value {
    display: block;
    font-size: 1.375rem;
    font-weight: 700;
    color: #EE2E24;
}

.participant-stat-label {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Detail Peserta Banner */
.participant-detail-banner {
    margin-bottom: 1.25rem;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 70%);
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 10px 30px rgba(238, 46, 36, 0.25);
}

.participant-detail-banner-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.participant-detail-banner-text {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.participant-detail-banner-title {
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.participant-detail-banner-subtitle {
    font-size: 0.85rem;
    opacity: 0.9;
}

/* Form Card */
.form-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.form-section {
    padding: 1.75rem 2rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.form-section:last-child {
    border-bottom: none;
}

.form-section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.form-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.1) 0%, rgba(196, 30, 26, 0.1) 100%);
    color: #EE2E24;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.form-section-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Participant Selector */
.participant-selector {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    max-height: 300px;
    overflow-y: auto;
}

.select-all-option {
    padding: 0.875rem 1rem;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
    border-radius: 10px;
    margin-bottom: 0.75rem;
    border: 1px solid rgba(238, 46, 36, 0.1);
}

.participant-checkbox {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.participant-checkbox:hover {
    background: rgba(238, 46, 36, 0.03);
}

.participant-checkbox.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f9fafb;
}

.participant-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 0.875rem;
    cursor: pointer;
    accent-color: #EE2E24;
}

.participant-checkbox input[type="checkbox"]:disabled {
    cursor: not-allowed;
}

.participant-checkbox-label {
    font-size: 0.9375rem;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-label .required {
    color: #EE2E24;
}

.form-control, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.9375rem;
    line-height: 1.6;
    letter-spacing: 0.01em;
    transition: all 0.2s ease;
    background: white;
    color: #1f2937;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #EE2E24;
    box-shadow: 0 0 0 3px rgba(238, 46, 36, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
    line-height: 1.7;
}

/* Buttons */
.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(238, 46, 36, 0.3);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Table Card */
.table-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.table-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.03) 0%, rgba(255, 255, 255, 0) 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.table-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-title i {
    color: #EE2E24;
    font-size: 1.1rem;
}

.table-title h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

/* Filters */
.filter-bar {
    padding: 1.25rem 1.5rem;
    background: #f9fafb;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filter-group select {
    width: 100%;
    padding: 0.625rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
}

.filter-group select:focus {
    outline: none;
    border-color: #EE2E24;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f9fafb;
}

.data-table th {
    padding: 1rem 1.25rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.data-table td {
    padding: 1rem 1.25rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.875rem;
    color: #1f2937;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    vertical-align: middle;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: rgba(238, 46, 36, 0.02);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

/* Badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 500;
    white-space: nowrap;
}

.badge-primary {
    background: rgba(238, 46, 36, 0.1);
    color: #EE2E24;
}

.badge-success {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.badge-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #D97706;
}

.badge-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #DC2626;
}

.badge-info {
    background: rgba(59, 130, 246, 0.1);
    color: #2563EB;
}

/* Action Buttons */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-outline {
    background: white;
    color: #EE2E24;
    border: 1.5px solid #e5e7eb;
}

.btn-outline:hover {
    border-color: #EE2E24;
    background: rgba(238, 46, 36, 0.05);
}

.btn-success {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}

/* Grade Input */
.grade-input {
    width: 80px;
    padding: 0.5rem 0.75rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
}

.grade-input:focus {
    outline: none;
    border-color: #EE2E24;
}

.feedback-input {
    width: 180px;
    padding: 0.5rem 0.75rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.875rem;
    letter-spacing: 0.01em;
}

.feedback-input:focus {
    outline: none;
    border-color: #EE2E24;
}

/* Submission Info */
.submission-info {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.deadline-overdue {
    color: #DC2626;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.grade-display {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 1.1rem;
    font-weight: 700;
    color: #EE2E24;
}

input[type="date"] {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    letter-spacing: 0.02em;
}

/* Action Buttons Group */
.action-btns {
    display: flex;
    gap: 0.375rem;
    align-items: center;
}

.btn-delete {
    color: #DC2626 !important;
    border-color: rgba(220, 38, 38, 0.2) !important;
}

.btn-delete:hover {
    background: rgba(220, 38, 38, 0.08) !important;
    border-color: #DC2626 !important;
}

.btn-danger-solid {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1.25rem;
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger-solid:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Custom Popup Overlay */
.popup-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    animation: popupFadeIn 0.2s ease;
}

.popup-overlay.active {
    display: flex;
}

@keyframes popupFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes popupSlideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.popup-card {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    animation: popupSlideIn 0.25s ease;
}

.popup-card.popup-lg {
    max-width: 700px;
}

.popup-card.popup-sm {
    max-width: 400px;
}

.popup-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.popup-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.1), rgba(196, 30, 26, 0.1));
    color: #EE2E24;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.popup-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.popup-subtitle {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}

.popup-close {
    margin-left: auto;
    align-self: flex-start;
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    line-height: 1;
    border-radius: 6px;
    transition: all 0.15s;
}

.popup-close:hover {
    color: #1f2937;
    background: rgba(0, 0, 0, 0.05);
}

.popup-body {
    padding: 1.5rem;
}

.popup-footer {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 0 0 16px 16px;
}

/* Detail Grid */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.detail-label i {
    font-size: 0.7rem;
    color: #9ca3af;
}

.detail-value {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #1f2937;
}

.detail-description {
    margin-bottom: 1.25rem;
}

.detail-desc-content {
    margin-top: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.7;
    white-space: pre-wrap;
}

.detail-files {
    margin-bottom: 0.5rem;
}

.detail-files-list {
    margin-top: 0.5rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Delete Modal */
.delete-icon-wrap {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: rgba(220, 38, 38, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #DC2626;
}

@media (max-width: 576px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .action-btns {
        flex-direction: column;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, rgba(238, 46, 36, 0.1) 0%, rgba(196, 30, 26, 0.1) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #EE2E24;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-size: 0.9375rem;
    color: #6b7280;
    margin: 0;
}


/* Form Actions */
.form-actions {
    padding: 1.5rem 2rem;
    background: #f9fafb;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Loading State */
.btn-loading {
    display: none;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}

/* Responsive */
@media (max-width: 1024px) {
    .tabs-card {
        flex-direction: column;
    }

    .tab-btn {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .mentor-hero {
        padding: 1.5rem;
    }

    .hero-text h1 {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .participants-grid {
        grid-template-columns: 1fr;
    }

    .form-section {
        padding: 1.25rem;
    }

    .filter-bar {
        flex-direction: column;
    }

    .filter-group {
        min-width: 100%;
    }

    .data-table {
        display: block;
        overflow-x: auto;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="mentor-hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1><i class="fas fa-tasks"></i> Penugasan & Penilaian</h1>
            <p>Kelola tugas dan berikan penilaian untuk peserta magang di divisi Anda</p>
        </div>
    </div>
</div>

@if($participants->isEmpty())
    {{-- Empty State --}}
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Belum Ada Peserta</h3>
        <p>Belum ada peserta magang yang diterima di divisi Anda.</p>
    </div>
@else
    {{-- Statistics Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $activeParticipants }}</h3>
                <p>Peserta Aktif</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $totalTasks }}</h3>
                <p>Total Tugas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $pendingGrading }}</h3>
                <p>Menunggu Dinilai</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $completedTasks }}</h3>
                <p>Sudah Dinilai</p>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="tabs-card">
        <button class="tab-btn active" data-tab="overview" onclick="switchTab('overview', this)">
            <i class="fas fa-th-large"></i> Overview Peserta
        </button>
        <button class="tab-btn" data-tab="create" onclick="switchTab('create', this)">
            <i class="fas fa-plus-circle"></i> Buat Tugas Baru
        </button>
        <button class="tab-btn" data-tab="tasks" onclick="switchTab('tasks', this)">
            <i class="fas fa-list"></i> Semua Tugas
            <span class="badge-count">{{ $totalTasks }}</span>
        </button>
        <button class="tab-btn" data-tab="grading" onclick="switchTab('grading', this)">
            <i class="fas fa-star"></i> Penilaian
            @if($pendingGrading > 0)
                <span class="badge-count">{{ $pendingGrading }}</span>
            @endif
        </button>
    </div>

    {{-- Tab: Overview Peserta --}}
    <div id="tab-overview" class="tab-content active">
        <div class="participant-detail-banner">
            <div class="participant-detail-banner-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="participant-detail-banner-text">
                <div class="participant-detail-banner-title">Detail Peserta</div>
                <div class="participant-detail-banner-subtitle">
                    Pilih salah satu peserta untuk melihat tugas dan status penilaiannya.
                </div>
            </div>
        </div>
        <div class="participants-grid">
            @foreach($participants as $participant)
                @php
                    $totalTugas = $participant->user->assignments->count();
                    $tugasSelesai = $participant->user->assignments->where('grade', '!=', null)->count();
                    $rataRata = $participant->user->assignments->whereNotNull('grade')->avg('grade');

                    $hasStarted = true;
                    if ($participant->start_date) {
                        $hasStarted = \Carbon\Carbon::parse($participant->start_date)->lte(now());
                    }
                @endphp
                <div class="participant-card {{ !$hasStarted ? 'not-started' : '' }}"
                     @if($hasStarted) onclick="viewParticipantDetail({{ $participant->user->id }})" @endif>
                    <div class="participant-header">
                        <div class="participant-avatar">
                            @if($participant->user->profile_picture)
                                <img src="{{ asset('storage/' . $participant->user->profile_picture) }}" alt="{{ $participant->user->name }}">
                            @else
                                {{ strtoupper(substr($participant->user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div class="participant-info">
                            <h3 class="participant-name">
                                {{ $participant->user->name ?? '-' }}
                                @if(!$hasStarted)
                                    <span class="badge-not-started">
                                        <i class="fas fa-clock"></i> Belum Mulai
                                    </span>
                                @endif
                            </h3>
                            <p class="participant-nim">{{ $participant->user->nim ?? '-' }}</p>
                            @if(!$hasStarted && $participant->start_date)
                                <p class="participant-start-date">
                                    <i class="fas fa-calendar"></i>
                                    Mulai: {{ \Carbon\Carbon::parse($participant->start_date)->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="participant-stats-row">
                        <div class="participant-stat">
                            <span class="participant-stat-value">{{ $totalTugas }}</span>
                            <span class="participant-stat-label">Tugas</span>
                        </div>
                        <div class="participant-stat">
                            <span class="participant-stat-value">{{ $tugasSelesai }}</span>
                            <span class="participant-stat-label">Selesai</span>
                        </div>
                        <div class="participant-stat">
                            <span class="participant-stat-value">{{ $rataRata ? number_format($rataRata, 0) : '-' }}</span>
                            <span class="participant-stat-label">Rata-rata</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tab: Buat Tugas Baru --}}
    <div id="tab-create" class="tab-content">
        <div class="form-card">
            <form method="POST" action="{{ route('mentor.penugasan.tambah') }}" enctype="multipart/form-data" id="createTaskForm">
                @csrf

                {{-- Section 1: Pilih Peserta --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="form-section-title">1. Pilih Peserta</h3>
                            <p class="form-section-subtitle">Tentukan peserta yang akan menerima tugas</p>
                        </div>
                    </div>
                    <div class="participant-selector">
                        <div class="select-all-option">
                            <label class="participant-checkbox">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                                <span class="participant-checkbox-label">
                                    <strong>Pilih Semua Peserta</strong>
                                    <span style="color: #6b7280; font-size: 0.8rem;">(yang sudah mulai)</span>
                                </span>
                            </label>
                        </div>
                        @foreach($participants as $participant)
                            @php
                                $hasStarted = true;
                                if ($participant->start_date) {
                                    $hasStarted = \Carbon\Carbon::parse($participant->start_date)->lte(now());
                                }
                            @endphp
                            <label class="participant-checkbox {{ !$hasStarted ? 'disabled' : '' }}">
                                <input type="checkbox"
                                       name="user_ids[]"
                                       value="{{ $participant->user->id }}"
                                       class="participant-check"
                                       {{ !$hasStarted ? 'disabled' : '' }}>
                                <span class="participant-checkbox-label">
                                    {{ $participant->user->name ?? '-' }} ({{ $participant->user->nim ?? '-' }})
                                    @if(!$hasStarted)
                                        <span class="badge-not-started">
                                            <i class="fas fa-clock"></i>
                                            Belum Mulai
                                            @if($participant->start_date)
                                                - {{ \Carbon\Carbon::parse($participant->start_date)->format('d M') }}
                                            @endif
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Section 2: Detail Tugas --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon">
                            <i class="fas fa-clipboard"></i>
                        </div>
                        <div>
                            <h3 class="form-section-title">2. Detail Tugas</h3>
                            <p class="form-section-subtitle">Isi informasi detail tugas yang akan diberikan</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Jenis Tugas <span class="required">*</span></label>
                                <select name="assignment_type" class="form-select" id="assignmentType" required>
                                    <option value="">Pilih Jenis Tugas</option>
                                    <option value="tugas_harian">Tugas Harian</option>
                                    <option value="tugas_proyek">Tugas Proyek</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Judul Tugas <span class="required">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Masukkan judul tugas..." required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Deadline <span class="required">*</span></label>
                                <input type="date" name="deadline" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6" id="presentationDateGroup" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Tanggal Presentasi</label>
                                <input type="date" name="presentation_date" class="form-control" id="presentationDate">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">File Tugas <span style="color: #6b7280; font-weight: 400;">(Opsional)</span></label>
                                <input type="file" name="file_path" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Deskripsi Tugas</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan detail tugas, instruksi pengerjaan, atau kriteria penilaian..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="submitTaskBtn">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane"></i> Buat Tugas
                        </span>
                        <span class="btn-loading">
                            <span class="spinner-border spinner-border-sm me-2"></span> Membuat tugas...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tab: Semua Tugas --}}
    <div id="tab-tasks" class="tab-content">
        <div class="table-card">
            <div class="filter-bar">
                <div class="filter-group">
                    <label>Filter Peserta</label>
                    <select id="filterPeserta" onchange="filterTasks()">
                        <option value="">Semua Peserta</option>
                        @foreach($participants as $participant)
                            <option value="{{ $participant->user->id }}">{{ $participant->user->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Filter Jenis</label>
                    <select id="filterJenis" onchange="filterTasks()">
                        <option value="">Semua Jenis</option>
                        <option value="tugas_harian">Tugas Harian</option>
                        <option value="tugas_proyek">Tugas Proyek</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Filter Status</label>
                    <select id="filterStatus" onchange="filterTasks()">
                        <option value="">Semua Status</option>
                        <option value="belum_dikerjakan">Belum Dikerjakan</option>
                        <option value="sudah_submit">Sudah Submit</option>
                        <option value="sudah_dinilai">Sudah Dinilai</option>
                        <option value="revisi">Revisi</option>
                    </select>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Peserta</th>
                        <th>Judul Tugas</th>
                        <th style="width: 120px;">Jenis</th>
                        <th style="width: 120px;">Deadline</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 80px;">Nilai</th>
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="taskTableBody">
                    @php $no = 1; @endphp
                    @foreach($participants as $participant)
                        @foreach($participant->user->assignments as $assignment)
                            @php
                                $hasSubmissions = $assignment->submissions && $assignment->submissions->count() > 0;
                                $latestSubmission = $hasSubmissions ? $assignment->submissions->sortByDesc('submitted_at')->first() : null;

                                $status = 'belum_dikerjakan';
                                if ((int) $assignment->is_revision === 1) {
                                    $status = 'revisi';
                                } elseif ($assignment->grade !== null) {
                                    $status = 'sudah_dinilai';
                                } elseif ($hasSubmissions || $assignment->submission_file_path) {
                                    $status = 'sudah_submit';
                                }
                            @endphp
                            <tr class="task-row"
                                data-peserta="{{ $participant->user->id }}"
                                data-jenis="{{ $assignment->assignment_type }}"
                                data-status="{{ $status }}">
                                <td>{{ $no++ }}</td>
                                <td>{{ $participant->user->name ?? '-' }}</td>
                                <td>
                                    {{ $assignment->title ?? '-' }}
                                    @if($hasSubmissions)
                                        <div class="submission-info">
                                            <i class="fas fa-upload"></i>
                                            {{ $latestSubmission->submitted_at ? \Carbon\Carbon::parse($latestSubmission->submitted_at)->format('d/m/Y H:i') : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->assignment_type === 'tugas_harian')
                                        <span class="badge badge-primary"><i class="fas fa-calendar-day"></i> Harian</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-project-diagram"></i> Proyek</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('d/m/Y') : '-' }}
                                    @if($assignment->deadline)
                                        @php
                                            $deadline = \Carbon\Carbon::parse($assignment->deadline);
                                            $isOverdue = $deadline->lt(now()) && $status === 'belum_dikerjakan';
                                        @endphp
                                        @if($isOverdue)
                                            <div class="deadline-overdue">
                                                <i class="fas fa-exclamation-triangle"></i> Terlambat
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($status === 'revisi')
                                        <span class="badge badge-danger"><i class="fas fa-redo"></i> Revisi</span>
                                    @elseif($status === 'sudah_dinilai')
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Dinilai</span>
                                    @elseif($status === 'sudah_submit')
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Menunggu Nilai</span>
                                    @else
                                        <span class="badge badge-info"><i class="fas fa-hourglass-half"></i> Belum Dikerjakan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->grade !== null)
                                        <span class="grade-display">{{ $assignment->grade }}</span>
                                    @else
                                        <span style="color: #9ca3af;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-action btn-outline btn-sm" title="Lihat Detail"
                                            onclick="viewTaskDetail({{ $assignment->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-outline btn-sm" title="Edit Tugas"
                                            onclick="editTask({{ $assignment->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-outline btn-sm btn-delete" title="Hapus Tugas"
                                            onclick="confirmDeleteTask({{ $assignment->id }})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Penilaian --}}
    <div id="tab-grading" class="tab-content">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-star"></i>
                    <h3>Tugas Menunggu Penilaian</h3>
                </div>
            </div>
            @if(session('revision_set_assignment_id'))
                <div style="padding: 0.75rem 1.5rem 0; color: #16a34a; font-size: 0.85rem;">
                    <i class="fas fa-info-circle"></i>
                    Status revisi sudah ditetapkan. Silakan isi feedback pada tugas terkait lalu tekan tombol Simpan.
                </div>
            @endif
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Peserta</th>
                        <th>Judul Tugas</th>
                        <th style="width: 100px;">Jenis</th>
                        <th style="width: 120px;">File</th>
                        <th style="width: 100px;">Nilai</th>
                        <th style="width: 200px;">Feedback</th>
                        <th style="width: 110px;">Revisi</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noGrade = 1; @endphp
                    @foreach($participants as $participant)
                        @foreach($participant->user->assignments as $assignment)
                            @php
                                $hasSubmissions = $assignment->submissions && $assignment->submissions->count() > 0;
                                $latestSubmission = $hasSubmissions ? $assignment->submissions->sortByDesc('submitted_at')->first() : null;

                                $perluNilai = false;
                                if ($hasSubmissions || $assignment->submission_file_path) {
                                    if (is_null($assignment->grade) && (int) $assignment->is_revision !== 1) {
                                        $perluNilai = true;
                                    } elseif ((int) $assignment->is_revision === 1 && empty($assignment->feedback)) {
                                        $perluNilai = true;
                                    }
                                }
                            @endphp
                            @if($perluNilai)
                                <tr>
                                    <td>{{ $noGrade++ }}</td>
                                    <td>{{ $participant->user->name ?? '-' }}</td>
                                    <td>
                                        {{ $assignment->title ?? '-' }}
                                        @if($hasSubmissions && $latestSubmission->submitted_at)
                                            <div class="submission-info">
                                                <i class="fas fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($latestSubmission->submitted_at)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->assignment_type === 'tugas_harian')
                                            <span class="badge badge-primary"><i class="fas fa-calendar-day"></i> Harian</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-project-diagram"></i> Proyek</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasSubmissions)
                                            <a href="{{ asset('storage/' . $latestSubmission->file_path) }}" target="_blank" class="btn-action btn-outline btn-sm">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        @elseif($assignment->submission_file_path)
                                            <a href="{{ asset('storage/' . $assignment->submission_file_path) }}" target="_blank" class="btn-action btn-outline btn-sm">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('mentor.penugasan.nilai', $assignment->id) }}" class="d-inline grade-form">
                                            @csrf
                                            <input type="number" name="grade" class="grade-input"
                                                placeholder="0-100" min="0" max="100"
                                                value="{{ $assignment->grade ?? '' }}"
                                                @if((int) $assignment->is_revision === 1) disabled @else required @endif>
                                    </td>
                                    <td>
                                            <input type="text" name="feedback" class="feedback-input"
                                                placeholder="Feedback"
                                                value="{{ $assignment->feedback ?? '' }}"
                                                @if((int) $assignment->is_revision === 1) required @endif>
                                    </td>
                                    <td>
                                            <button type="submit" class="btn-action btn-success btn-sm grade-submit-btn">
                                                <span class="btn-text">
                                                    <i class="fas fa-save"></i> Simpan
                                                </span>
                                                <span class="btn-loading">
                                                    <span class="spinner-border spinner-border-sm"></span>
                                                </span>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('mentor.penugasan.revisi', $assignment->id) }}" class="d-inline revision-form">
                                            @csrf
                                            <input type="hidden" name="is_revision" value="1">
                                            <input type="hidden" name="feedback" value="">
                                            <button type="submit"
                                                class="btn-danger-solid"
                                                @if((int) $assignment->is_revision === 1) disabled @endif
                                                onclick="prepareRevisionSubmit(this)"
                                                title="Tandai tugas sebagai revisi">
                                                <i class="fas fa-redo"></i> Revisi
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    @if($pendingGrading === 0)
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 3rem;">
                                <div class="empty-icon" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <p style="margin-top: 1rem; color: #6b7280;">Semua tugas sudah dinilai</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection

@push('scripts')
{{-- Modals must be outside @section('content') to avoid stacking context issues --}}

{{-- Modal Detail Participant --}}
<div class="modal fade" id="participantDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" style="position: absolute; top: 1rem; right: 1rem; z-index: 10;"></button>
            <div class="modal-body" id="participantDetailContent">
                {{-- Content will be loaded here --}}
            </div>
        </div>
    </div>
</div>

{{-- Popup View Task Detail --}}
<div class="popup-overlay" id="taskDetailPopup">
    <div class="popup-card popup-lg">
        <div class="popup-header">
            <div class="popup-header-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <h5 class="popup-title" id="detailModalTitle">Detail Tugas</h5>
                <p class="popup-subtitle" id="detailModalPeserta"></p>
            </div>
            <button type="button" class="popup-close" onclick="closePopup('taskDetailPopup')">&times;</button>
        </div>
        <div class="popup-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-tag"></i> Jenis Tugas</span>
                    <span class="detail-value" id="detailType"></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-calendar"></i> Deadline</span>
                    <span class="detail-value" id="detailDeadline"></span>
                </div>
                <div class="detail-item" id="detailPresentationRow" style="display: none;">
                    <span class="detail-label"><i class="fas fa-chalkboard"></i> Tanggal Presentasi</span>
                    <span class="detail-value" id="detailPresentation"></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-info-circle"></i> Status</span>
                    <span id="detailStatus"></span>
                </div>
                <div class="detail-item" id="detailGradeRow" style="display: none;">
                    <span class="detail-label"><i class="fas fa-star"></i> Nilai</span>
                    <span class="detail-value grade-display" id="detailGrade"></span>
                </div>
                <div class="detail-item" id="detailFeedbackRow" style="display: none;">
                    <span class="detail-label"><i class="fas fa-comment"></i> Feedback</span>
                    <span class="detail-value" id="detailFeedback"></span>
                </div>
            </div>
            <div class="detail-description">
                <span class="detail-label"><i class="fas fa-align-left"></i> Deskripsi</span>
                <div class="detail-desc-content" id="detailDescription"></div>
            </div>
            <div class="detail-files" id="detailFilesSection" style="display: none;">
                <span class="detail-label"><i class="fas fa-paperclip"></i> File</span>
                <div class="detail-files-list" id="detailFilesList"></div>
            </div>
        </div>
    </div>
</div>

{{-- Popup Edit Task --}}
<div class="popup-overlay" id="editTaskPopup">
    <div class="popup-card popup-lg">
        <div class="popup-header">
            <div class="popup-header-icon" style="background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(37,99,235,0.15)); color: #2563EB;">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h5 class="popup-title">Edit Tugas</h5>
                <p class="popup-subtitle">Perbarui detail tugas</p>
            </div>
            <button type="button" class="popup-close" onclick="closePopup('editTaskPopup')">&times;</button>
        </div>
        <form method="POST" id="editTaskForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="popup-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Jenis Tugas <span class="required">*</span></label>
                            <select name="assignment_type" class="form-select" id="editAssignmentType" required>
                                <option value="tugas_harian">Tugas Harian</option>
                                <option value="tugas_proyek">Tugas Proyek</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Judul Tugas <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" id="editTitle" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Deadline <span class="required">*</span></label>
                            <input type="date" name="deadline" class="form-control" id="editDeadline" required>
                        </div>
                    </div>
                    <div class="col-md-6" id="editPresentationDateGroup" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Presentasi</label>
                            <input type="date" name="presentation_date" class="form-control" id="editPresentationDate">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ganti File Tugas <span style="color: #6b7280; font-weight: 400;">(Opsional)</span></label>
                            <input type="file" name="file_path" class="form-control">
                            <div id="editCurrentFile" style="margin-top: 0.5rem; font-size: 0.8rem; color: #6b7280;"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Deskripsi Tugas</label>
                            <textarea name="description" class="form-control" id="editDescription" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="popup-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn-action btn-outline" onclick="closePopup('editTaskPopup')">Batal</button>
                <button type="submit" class="btn-submit" id="editSubmitBtn">
                    <span class="btn-text"><i class="fas fa-save"></i> Simpan Perubahan</span>
                    <span class="btn-loading"><span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Popup Confirm Delete --}}
<div class="popup-overlay" id="deleteTaskPopup">
    <div class="popup-card popup-sm">
        <div class="popup-body" style="padding: 2rem; text-align: center;">
            <div class="delete-icon-wrap">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h5 style="font-weight: 600; margin-bottom: 0.5rem;">Hapus Tugas?</h5>
            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
                Tugas "<span id="deleteTaskTitle"></span>" akan dihapus permanen beserta submission-nya.
            </p>
            <form method="POST" id="deleteTaskForm">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 0.75rem; justify-content: center;">
                    <button type="button" class="btn-action btn-outline" onclick="closePopup('deleteTaskPopup')">Batal</button>
                    <button type="submit" class="btn-action btn-danger-solid">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Popup open/close helpers
function openPopup(popupId) {
    var el = document.getElementById(popupId);
    if (el) {
        el.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closePopup(popupId) {
    var el = document.getElementById(popupId);
    if (el) {
        el.classList.remove('active');
        // Only restore scroll if no other popups are open
        if (!document.querySelector('.popup-overlay.active')) {
            document.body.style.overflow = '';
        }
    }
}

// Close popup when clicking overlay background
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay') && e.target.classList.contains('active')) {
        closePopup(e.target.id);
    }
});

// Close popup on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var activePopup = document.querySelector('.popup-overlay.active');
        if (activePopup) {
            closePopup(activePopup.id);
        }
    }
});

// Assignment data store (safe JSON, no inline JS escaping issues)
var taskDataStore = @php
    $store = [];
    foreach ($participants as $participant) {
        foreach ($participant->user->assignments as $assignment) {
            $hasSub = $assignment->submissions && $assignment->submissions->count() > 0;
            $latestSub = $hasSub ? $assignment->submissions->sortByDesc('submitted_at')->first() : null;
            $st = 'belum_dikerjakan';
            if ((int) $assignment->is_revision === 1) { $st = 'revisi'; }
            elseif ($assignment->grade !== null) { $st = 'sudah_dinilai'; }
            elseif ($hasSub || $assignment->submission_file_path) { $st = 'sudah_submit'; }

            $store[$assignment->id] = [
                'title' => $assignment->title ?? '-',
                'peserta' => $participant->user->name ?? '-',
                'type' => $assignment->assignment_type === 'tugas_harian' ? 'Tugas Harian' : 'Tugas Proyek',
                'deadline' => $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('d M Y') : '-',
                'presentationDate' => $assignment->presentation_date ? \Carbon\Carbon::parse($assignment->presentation_date)->format('d M Y') : '',
                'description' => $assignment->description ?? 'Tidak ada deskripsi',
                'status' => $st,
                'grade' => $assignment->grade ?? '',
                'feedback' => $assignment->feedback ?? '',
                'filePath' => $assignment->file_path ? asset('storage/' . $assignment->file_path) : '',
                'submissionPath' => $hasSub && $latestSub && $latestSub->file_path ? asset('storage/' . $latestSub->file_path) : ($assignment->submission_file_path ? asset('storage/' . $assignment->submission_file_path) : ''),
                'submittedAt' => $hasSub && $latestSub && $latestSub->submitted_at ? \Carbon\Carbon::parse($latestSub->submitted_at)->format('d M Y H:i') : '',
                'submissions' => $hasSub ? $assignment->submissions->sortBy('submitted_at')->map(function($sub, $index) {
                    return [
                        'file_path' => $sub->file_path ? asset('storage/' . $sub->file_path) : '',
                        'submitted_at' => $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y H:i') : '',
                    ];
                })->values()->all() : [],
            ];
        }
    }
    echo json_encode($store);
@endphp;

// Tab switching
function switchTab(tabName, sourceBtn = null) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    var targetTab = document.getElementById('tab-' + tabName);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Activate the correct button
    if (sourceBtn) {
        sourceBtn.classList.add('active');
    } else {
        var autoBtn = document.querySelector('.tab-btn[data-tab=\"' + tabName + '\"]');
        if (autoBtn) {
            autoBtn.classList.add('active');
        }
    }
}

// Aktifkan tab Penilaian otomatis setelah set revisi
document.addEventListener('DOMContentLoaded', function() {
    @if(session('revision_set_assignment_id'))
        switchTab('grading');
    @endif
});

// Select all participants (only enabled ones)
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.participant-check:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Show/hide presentation date based on task type
document.getElementById('assignmentType')?.addEventListener('change', function() {
    const presentationGroup = document.getElementById('presentationDateGroup');
    const presentationDate = document.getElementById('presentationDate');

    if (this.value === 'tugas_proyek') {
        presentationGroup.style.display = 'block';
    } else {
        presentationGroup.style.display = 'none';
        presentationDate.value = '';
    }
});

// Filter tasks
function filterTasks() {
    const filterPeserta = document.getElementById('filterPeserta').value;
    const filterJenis = document.getElementById('filterJenis').value;
    const filterStatus = document.getElementById('filterStatus').value;

    const rows = document.querySelectorAll('.task-row');

    rows.forEach(row => {
        let show = true;

        if (filterPeserta && row.dataset.peserta !== filterPeserta) {
            show = false;
        }

        if (filterJenis && row.dataset.jenis !== filterJenis) {
            show = false;
        }

        if (filterStatus && row.dataset.status !== filterStatus) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

// View participant detail
function viewParticipantDetail(userId) {
    // Switch to "Semua Tugas" tab and filter by this participant
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    document.getElementById('tab-tasks').classList.add('active');
    document.querySelectorAll('.tab-btn')[2].classList.add('active');

    // Filter by participant
    const filterPeserta = document.getElementById('filterPeserta');
    if (filterPeserta) {
        filterPeserta.value = userId;
        filterTasks();
    }
}

// Form validation and loading state
const createTaskForm = document.getElementById('createTaskForm');
const submitTaskBtn = document.getElementById('submitTaskBtn');

if (createTaskForm && submitTaskBtn) {
    let formSubmitted = false;

    createTaskForm.addEventListener('submit', function(e) {
        if (formSubmitted) {
            e.preventDefault();
            return false;
        }

        // Validate participant selection
        const checkedBoxes = document.querySelectorAll('.participant-check:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu peserta untuk ditugaskan!');
            return false;
        }

        // Show loading state
        const btnText = submitTaskBtn.querySelector('.btn-text');
        const btnLoading = submitTaskBtn.querySelector('.btn-loading');

        if (btnText && btnLoading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            submitTaskBtn.disabled = true;
        }

        formSubmitted = true;
        return true;
    });
}

// Grade form loading state
document.querySelectorAll('.grade-form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.grade-submit-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');

        if (btnText && btnLoading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            btn.disabled = true;
        }
    });
});

// Support 2 flows for revisi:
// 1) Mentor isi feedback dulu lalu klik "Revisi" (feedback ikut tersimpan + status revisi)
// 2) Mentor klik "Revisi" dulu lalu isi feedback dan klik "Simpan"
function prepareRevisionSubmit(btnEl) {
    try {
        const row = btnEl.closest('tr');
        const revisionForm = btnEl.closest('form');
        if (!row || !revisionForm) return true;

        const feedbackInput = row.querySelector('input[name="feedback"]');
        const hiddenFeedback = revisionForm.querySelector('input[type="hidden"][name="feedback"]');

        if (hiddenFeedback && feedbackInput) {
            hiddenFeedback.value = feedbackInput.value || '';
        }
    } catch (e) {
        // no-op
    }
    return true;
}

// View task detail (lookup from data store by assignment ID)
function viewTaskDetail(assignmentId) {
    var data = taskDataStore[assignmentId];
    if (!data) { alert('Data tugas tidak ditemukan.'); return; }

    document.getElementById('detailModalTitle').textContent = data.title;
    document.getElementById('detailModalPeserta').textContent = 'Peserta: ' + data.peserta;
    document.getElementById('detailType').textContent = data.type;
    document.getElementById('detailDeadline').textContent = data.deadline;
    document.getElementById('detailDescription').textContent = data.description;

    // Presentation date
    var presRow = document.getElementById('detailPresentationRow');
    if (data.presentationDate) {
        presRow.style.display = '';
        document.getElementById('detailPresentation').textContent = data.presentationDate;
    } else {
        presRow.style.display = 'none';
    }

    // Status badge
    var statusMap = {
        'belum_dikerjakan': '<span class="badge badge-info"><i class="fas fa-hourglass-half"></i> Belum Dikerjakan</span>',
        'sudah_submit': '<span class="badge badge-warning"><i class="fas fa-clock"></i> Menunggu Nilai</span>',
        'sudah_dinilai': '<span class="badge badge-success"><i class="fas fa-check"></i> Dinilai</span>',
        'revisi': '<span class="badge badge-danger"><i class="fas fa-redo"></i> Revisi</span>'
    };
    document.getElementById('detailStatus').innerHTML = statusMap[data.status] || data.status;

    // Grade
    var gradeRow = document.getElementById('detailGradeRow');
    if (data.grade) {
        gradeRow.style.display = '';
        document.getElementById('detailGrade').textContent = data.grade;
    } else {
        gradeRow.style.display = 'none';
    }

    // Feedback
    var feedbackRow = document.getElementById('detailFeedbackRow');
    if (data.feedback) {
        feedbackRow.style.display = '';
        document.getElementById('detailFeedback').textContent = data.feedback;
    } else {
        feedbackRow.style.display = 'none';
    }

    // Files
    var filesSection = document.getElementById('detailFilesSection');
    var filesList = document.getElementById('detailFilesList');
    filesList.innerHTML = '';
    var hasFiles = false;

    // File tugas dari mentor
    if (data.filePath) {
        hasFiles = true;
        filesList.innerHTML += '<a href="' + data.filePath + '" target="_blank" class="btn-action btn-outline btn-sm"><i class="fas fa-download"></i> File Tugas</a>';
    }

    // Semua submission peserta (pertama + revisi)
    if (Array.isArray(data.submissions) && data.submissions.length > 0) {
        hasFiles = true;
        data.submissions.forEach(function(sub, index) {
            var label = index === 0 ? 'Kumpulan Pertama' : 'Revisi ' + index;
            var timeInfo = sub.submitted_at ? ' (' + sub.submitted_at + ')' : '';
            if (sub.file_path) {
                filesList.innerHTML += '<a href="' + sub.file_path + '" target="_blank" class="btn-action btn-success btn-sm"><i class="fas fa-download"></i> ' + label + timeInfo + '</a>';
            }
        });
    } else if (data.submissionPath) {
        // Fallback ke struktur lama bila ada
        hasFiles = true;
        filesList.innerHTML += '<a href="' + data.submissionPath + '" target="_blank" class="btn-action btn-success btn-sm"><i class="fas fa-download"></i> File Submission' + (data.submittedAt ? ' (' + data.submittedAt + ')' : '') + '</a>';
    }

    filesSection.style.display = hasFiles ? '' : 'none';

    openPopup('taskDetailPopup');
}

// Edit task
function editTask(assignmentId) {
    fetch('/mentor/penugasan/' + assignmentId + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const data = result.data;
            document.getElementById('editTaskForm').action = '/mentor/penugasan/' + assignmentId + '/update';
            document.getElementById('editTitle').value = data.title || '';
            document.getElementById('editAssignmentType').value = data.assignment_type || 'tugas_harian';
            document.getElementById('editDeadline').value = data.deadline || '';
            document.getElementById('editDescription').value = data.description || '';

            // Presentation date
            const presGroup = document.getElementById('editPresentationDateGroup');
            const presInput = document.getElementById('editPresentationDate');
            if (data.assignment_type === 'tugas_proyek') {
                presGroup.style.display = 'block';
                presInput.value = data.presentation_date || '';
            } else {
                presGroup.style.display = 'none';
                presInput.value = '';
            }

            // Current file indicator
            const currentFile = document.getElementById('editCurrentFile');
            if (data.file_path) {
                currentFile.innerHTML = '<i class="fas fa-paperclip"></i> File saat ini: <a href="/storage/' + data.file_path + '" target="_blank">Lihat file</a>';
            } else {
                currentFile.innerHTML = '';
            }

            openPopup('editTaskPopup');
        }
    })
    .catch(error => {
        alert('Gagal memuat data tugas. Silakan coba lagi.');
    });
}

// Toggle presentation date in edit modal
document.getElementById('editAssignmentType')?.addEventListener('change', function() {
    const presGroup = document.getElementById('editPresentationDateGroup');
    const presInput = document.getElementById('editPresentationDate');
    if (this.value === 'tugas_proyek') {
        presGroup.style.display = 'block';
    } else {
        presGroup.style.display = 'none';
        presInput.value = '';
    }
});

// Edit form submit with loading
const editTaskForm = document.getElementById('editTaskForm');
if (editTaskForm) {
    let editSubmitted = false;
    editTaskForm.addEventListener('submit', function(e) {
        if (editSubmitted) { e.preventDefault(); return false; }
        const btn = document.getElementById('editSubmitBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        if (btnText && btnLoading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            btn.disabled = true;
        }
        editSubmitted = true;
    });
}

// Confirm delete task (lookup title from data store)
function confirmDeleteTask(assignmentId) {
    var data = taskDataStore[assignmentId];
    document.getElementById('deleteTaskTitle').textContent = data ? data.title : 'tugas ini';
    document.getElementById('deleteTaskForm').action = '/mentor/penugasan/' + assignmentId + '/delete';
    openPopup('deleteTaskPopup');
}
</script>
@endpush
