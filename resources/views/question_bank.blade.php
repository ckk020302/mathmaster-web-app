@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<style>
    /* All styles embedded - guaranteed to work */
    body {
        background-color: #f8fafc;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
    }

    .container-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    /* Top Navigation */
    .nav-section {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
    }

    .nav-tabs-wrapper {
        display: flex;
        background: white;
        border-radius: 12px;
        padding: 8px;
        gap: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        text-decoration: none;
        color: #64748b;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .nav-link:hover {
        color: #1e293b;
        background: #f1f5f9;
        text-decoration: none;
    }

    .nav-link.active {
        background: #e2e8f0;
        color: #1e293b;
        font-weight: 600;
    }

    /* Main Question Bank Container */
    .qb-main-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 60px 40px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        position: relative;
    }

    /* Menu Container */
    .button-menu {
        display: flex;
        flex-direction: column;
        gap: 24px;
        align-items: center;
        width: 100%;
    }

    /* Beautiful Mint Green Buttons */
    .styled-button {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        max-width: 450px;
        padding: 24px 28px;
        background: #a7f3d0;
        border: 1px solid #6ee7b7;
        border-radius: 16px;
        text-decoration: none;
        color: #064e3b;
        transition: all 0.3s ease;
        font-size: 18px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(167, 243, 208, 0.3);
        position: relative;
        overflow: hidden;
    }

    .styled-button:hover {
        background: #6ee7b7;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(167, 243, 208, 0.4);
        text-decoration: none;
        color: #064e3b;
        border-color: #34d399;
    }

    .styled-button:active {
        transform: translateY(0px);
        box-shadow: 0 4px 12px rgba(167, 243, 208, 0.3);
    }

    .styled-button i {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #10b981;
        color: white;
        border-radius: 50%;
        margin-right: 20px;
        font-size: 22px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
    }

    .styled-button:hover i {
        background: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        transform: scale(1.05);
    }

    .styled-button span {
        font-size: 18px;
        font-weight: 600;
        color: #064e3b;
        letter-spacing: 0.3px;
    }

    /* Page Header for other pages */
    .page-header-section {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
        position: relative;
    }

    .header-title {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 14px;
        position: absolute;
        top: 0;
        right: 0;
    }

    .back-link:hover {
        background: #5a6268;
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .styled-button {
            padding: 20px 24px;
            font-size: 16px;
        }

        .styled-button i {
            width: 45px;
            height: 45px;
            font-size: 20px;
            margin-right: 16px;
        }

        .styled-button span {
            font-size: 16px;
        }
    }

    /* Search Experience */
    .search-page {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .search-header {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 24px;
        padding: 28px 32px;
        border-radius: 20px;
        background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        border: 1px solid #e0e7ff;
    }

    .search-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        border: 1px solid #c7d2fe;
        background: white;
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .search-back:hover {
        background: #e0e7ff;
        color: #1e3a8a;
        text-decoration: none;
    }

    .search-header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .search-header-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.28);
    }

    .search-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
    }

    .search-subtitle {
        margin: 4px 0 0 0;
        font-size: 14px;
        color: #475569;
    }

    .search-header-metrics {
        text-align: right;
    }

    .search-header-metrics .metric-label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 4px;
    }

    .search-header-metrics .metric-value {
        display: block;
        font-size: 28px;
        font-weight: 700;
        color: #1d4ed8;
        line-height: 1;
    }

    .metric-caption {
        display: block;
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .search-filters-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 28px 32px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px 24px;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #475569;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        font-size: 15px;
        color: #0f172a;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        background: white;
    }

    .filter-actions {
        margin-top: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
    }

    .filter-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.25);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 32px rgba(37, 99, 235, 0.3);
    }

    .filter-reset {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
        padding: 12px 16px;
    }

    .filter-reset:hover {
        color: #1d4ed8;
        text-decoration: none;
    }

    .search-results {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .results-summary {
        font-size: 14px;
        color: #475569;
    }

    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }

    .result-card {
        display: flex;
        flex-direction: column;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .result-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    }

    .result-card-media {
        position: relative;
        height: 180px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .result-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
    }

    .result-card-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        font-size: 13px;
    }

    .result-card-placeholder i {
        font-size: 24px;
    }

    .result-card-body {
        padding: 20px 20px 12px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .result-card-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        font-size: 12px;
    }

    .result-card-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .result-card-details {
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
    }

    .result-card-details div {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .result-card-details dt {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        margin: 0;
    }

    .result-card-details dd {
        margin: 0;
        font-size: 14px;
        color: #1f2937;
        font-weight: 500;
    }

    .result-card-footer {
        margin-top: auto;
        padding: 5px 5px 5px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
    }

    .result-card-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        border: none;
        background: #1d4ed8;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .result-card-action:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }

    .search-empty-state {
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        color: #64748b;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        background: white;
    }

    .search-empty-state i {
        font-size: 28px;
        color: #94a3b8;
    }

    @media (max-width: 1024px) {
        .search-header {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .search-header-metrics {
            text-align: left;
        }
    }

    @media (max-width: 640px) {
        .search-header-content {
            align-items: flex-start;
        }

        .search-filters-card {
            padding: 24px;
        }

        .filter-actions {
            justify-content: stretch;
        }

        .filter-submit {
            width: 100%;
            justify-content: center;
        }

        .filter-reset {
            width: 100%;
            justify-content: center;
        }

        .result-card-footer {
            justify-content: stretch;
        }

        .result-card-action {
            width: 100%;
            justify-content: center;
        }
    }
    /* Enhanced Form Styles */
    .enhanced-form-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 15px;
    }

    .enhanced-select,
    .enhanced-input {
        padding: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.2s ease;
        background: white;
    }

    .enhanced-select:focus,
    .enhanced-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .visual-section {
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        background: linear-gradient(145deg, #fafbfc 0%, #f9fafb 100%);
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        font-weight: 700;
        color: #1f2937;
        font-size: 18px;
    }

    .section-icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(145deg, #3b82f6, #2563eb);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 50px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .upload-area:hover {
        border-color: #3b82f6;
        background: linear-gradient(145deg, #f8faff, #f0f4ff);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    .upload-area.has-file {
        border-color: #10b981;
        background: linear-gradient(145deg, #f0fdf4, #ecfdf5);
    }

    .upload-icon {
        font-size: 40px;
        color: #9ca3af;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .upload-area:hover .upload-icon {
        color: #3b82f6;
        transform: scale(1.1);
    }

    .upload-area.has-file .upload-icon {
        color: #10b981;
    }

    .upload-text {
        font-size: 16px;
        color: #6b7280;
        font-weight: 500;
    }

    .answer-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 15px;
    }

    .answer-option {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        font-size: 18px;
        font-weight: 600;
        color: #374151;
        position: relative;
        overflow: hidden;
    }

    .answer-option:hover {
        border-color: #3b82f6;
        background: linear-gradient(145deg, #f8faff, #f0f4ff);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.15);
    }

    .answer-option.selected {
        border-color: #10b981;
        background: linear-gradient(145deg, #f0fdf4, #ecfdf5);
        color: #059669;
    }

    .answer-option input[type="radio"] {
        margin-right: 10px;
        transform: scale(1.3);
        accent-color: #10b981;
    }

    .current-image-section,
    .current-tips-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid #e2e8f0;
    }

    .current-image-section h4,
    .current-tips-section h4 {
        margin: 0 0 15px 0;
        color: #374151;
        font-weight: 600;
    }

    .btn-cancel:hover {
        background: #4b5563;
        text-decoration: none;
        color: white;
    }

    /* User Questions Styles */
    .user-questions-container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Filter Section */
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid #e2e8f0;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .filter-select,
    .filter-input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        transition: border-color 0.2s;
    }

    .filter-select:focus,
    .filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-btn,
    .clear-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }

    .filter-btn {
        background: #3b82f6;
        color: white;
    }

    .filter-btn:hover {
        background: #2563eb;
    }

    .clear-btn {
        background: #6b7280;
        color: white;
    }

    .clear-btn:hover {
        background: #4b5563;
        text-decoration: none;
        color: white;
    }

    /* Questions Grid */
    .questions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .question-card-user {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .question-card-user:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .question-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .academic-level {
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .difficulty-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .difficulty-easy {
        background: #dcfce7;
        color: #166534;
    }

    .difficulty-intermediate {
        background: #fef3c7;
        color: #92400e;
    }

    .difficulty-advanced {
        background: #fecaca;
        color: #991b1b;
    }

    .question-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
    }

    .edit-btn {
        background: #dbeafe;
        color: #1e40af;
    }

    .edit-btn:hover {
        background: #bfdbfe;
    }

    .delete-btn {
        background: #fecaca;
        color: #991b1b;
    }

    .delete-btn:hover {
        background: #fca5a5;
    }

    .view-btn {
        background: #d1fae5;
        color: #065f46;
    }

    .view-btn:hover {
        background: #a7f3d0;
    }

    .question-content {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .question-info {
        flex: 1;
    }



    .chapter-info {
        color: #6b7280;
        font-size: 14px;
        margin: 0 0 10px 0;
    }

    .question-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .answer-info,
    .upload-date {
        font-size: 12px;
        color: #6b7280;
    }

    .answer-info {
        font-weight: 600;
    }

    .question-thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        background: #f3f4f6;
        cursor: pointer;
    }

    .question-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .question-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .tip-indicators {
        display: flex;
        gap: 8px;
    }

    .tip-indicator {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .tip-easy {
        background: #dcfce7;
        color: #166534;
    }

    .tip-intermediate {
        background: #fef3c7;
        color: #92400e;
    }

    .tip-advanced {
        background: #fecaca;
        color: #991b1b;
    }

    /* Disable pointer events and change cursor for locked tips */
    .tip-upload-area.locked {
        pointer-events: none;
        opacity: 0.5;
        cursor: not-allowed;
    }

    .tip-preview {
        margin-top: 15px;
        display: flex;
        justify-content: center;
    }

    .tip-preview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 6px;
        object-fit: contain;
        border: 1px solid #ccc;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .tip-section {
        max-width: 600px;
        /* set max width to control size */
        margin: 20px auto;
        /* auto left-right margin centers it */
        padding: 10px 15px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    .tip-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* center all children horizontally */
        gap: 10px;
    }

    .tip-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: bold;
        font-size: 1.1rem;
        margin-bottom: 10px;
        color: #333;
    }


    .question-id {
        color: #9ca3af;
        font-size: 11px;
    }

    /* No Questions State */
    .no-questions-container {
        text-align: center;
        padding: 60px 20px;
    }

    .no-questions-content i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .no-questions-content h3 {
        font-size: 24px;
        color: #374151;
        margin-bottom: 10px;
    }

    .no-questions-content p {
        color: #6b7280;
        margin-bottom: 30px;
        font-size: 16px;
    }

    .create-first-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #10b981;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s;
    }

    .create-first-btn:hover {
        background: #059669;
        text-decoration: none;
        color: white;
    }

    /* Modal Styles */
    .question-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-header h3 {
        margin: 0;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .btn-cancel,
    .btn-delete {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.2s;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
    }


    /* Responsive */
    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }

        .questions-grid {
            grid-template-columns: 1fr;
        }

        .question-content {
            flex-direction: column;
        }

        .question-thumbnail {
            width: 100%;
            height: 150px;
            align-self: center;
        }

        .question-thumbnail.no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
        }

        .detail-row img {
            transition: transform 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .detail-row img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Make images clickable */
        .detail-row img {
            cursor: pointer;
        }

        /* Loading states */
        .question-card-user.deleting {
            opacity: 0.5;
            pointer-events: none;
            transition: all 0.3s ease;
        }

    .tip-upload-area {
        border: 2px dashed #a0aec0; /* A slightly more visible dashed border */
        border-radius: 14px; /* Larger border radius */
        padding: 60px; /* Significantly increased padding */
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fdfdfd; /* Lighter background */
        min-height: 180px; /* Increased minimum height */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%; /* Ensure it takes full width of its parent */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    }

    .tip-upload-area:hover {
        border-color: #4299e1; /* Blue border on hover */
        background: linear-gradient(145deg, #eff6ff, #e0e9ff); /* Light blue gradient on hover */
        transform: translateY(-1px); /* More pronounced lift effect */
        box-shadow: 0 4px 12px rgba(66, 153, 225, 0.15); /* Stronger shadow on hover */
    }

        .tip-upload-area.has-file {
            border-color: #10b981;
            background: linear-gradient(145deg, #f0fdf4, #ecfdf5);
        }

        .tip-upload-area .upload-icon {
        font-size: 48px; /* Larger icon */
        color: #9ca3af;
        margin-bottom: 20px; /* More space below icon */
    }

    .tip-upload-area .upload-text {
        font-size: 18px; /* Larger text */
        color: #6b7280;
        font-weight: 500;
    }
    
        .image-preview {
            max-width: 100%;
            max-height: 250px;
            border-radius: 12px;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 14px;
            margin-top: 8px;
            font-weight: 500;
        }

        /* File input styling */
        input[type="file"] {
            display: none;
        }

        /* Submit button styling - MAKE SURE THIS EXISTS */
        .submit-button,
        #submit_btn {
            background: linear-gradient(145deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 18px 50px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 40px auto 0;
            min-width: 200px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .submit-button:hover,
        #submit_btn:hover {
            background: linear-gradient(145deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* Fix the tip header icons */
        .tip-header i {
            color: #f59e0b;
            margin-right: 8px;
        }

        /* Enhanced select styling for dynamic chapters */
        select[disabled] {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            background-color: #f8f9fa !important;
        }

        select[disabled]:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .form-group select {
            transition: opacity 0.3s ease, background-color 0.3s ease;
        }

        /* Loading state for chapter select */
        .chapter-loading::after {
            content: " (Loading...)";
            color: #6c757d;
            font-style: italic;
        }

        .tip-display-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }

        .current-image-section,
        .current-tips-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .current-image-section h4,
        .current-tips-section h4 {
            margin: 0 0 15px 0;
            color: #374151;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #4b5563;
            text-decoration: none;
            color: white;
        }
        

    }
</style>

<div class="container-wrapper">
    @php
    $mode = request()->route()->getName();
    $isSearch = str_contains($mode, 'search');
    $isCreate = str_contains($mode, 'create');
    $isEdit = str_contains($mode, 'edit');
    $isShow = str_contains($mode, 'show');
    $isSubmitOptions = str_contains($mode, 'submit-options');
    $isUserQuestions = str_contains($mode, 'user-questions');
    $isIndex = str_contains($mode, 'index');
    @endphp

    @if($isIndex)
    <!-- Main Question Bank - Index Page (Clean, No Header) -->
    <div class="qb-main-container">
        <div class="button-menu">
            <a href="{{ route('questionbank.search') }}" class="styled-button">
                <i class="fas fa-search"></i>
                <span>Search Existing Questions</span>
            </a>

            <a href="{{ route('questionbank.submit-options') }}" class="styled-button">
                <i class="fas fa-plus"></i>
                <span>Create/Edit Questions</span>
            </a>
        </div>
    </div>
    @endif

    @if($isSubmitOptions)

    <!-- Fixed Back Button (Styled with Bootstrap) -->
    <div class="fixed-back-button">
        <a href="{{ route('questionbank.index') }}" class="btn btn-outline-primary">
            &larr; Back
        </a>
    </div>

    <!-- Submit Options Page -->
    <div class="qb-main-container">

        <div class="page-header-section">
            <div class="header-title">
                <i class="fas fa-edit"></i>
                Create/Edit Questions
            </div>
        </div>

        <div class="button-menu">
            <a href="{{ route('questionbank.create') }}" class="styled-button">
                <i class="fas fa-plus"></i>
                <span>Create New Question</span>
            </a>

            <a href="{{ route('questionbank.user-questions') }}" class="styled-button">
                <i class="fas fa-edit"></i>
                <span>Edit/Delete Questions</span>
            </a>
        </div>
    </div>

    @endif

    @if($isSearch)
    <div class="search-page">
        <div class="search-header">
            <a href="{{ route('questionbank.index') }}" class="search-back">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <div class="search-header-content">
                <div class="search-header-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div>
                    <h1 class="search-title">Search Questions</h1>
                    <p class="search-subtitle">Quickly browse the shared question bank with precise filters.</p>
                </div>
            </div>
            @if(isset($questions) && method_exists($questions, 'total'))
            <div class="search-header-metrics">
                <span class="metric-label">Results</span>
                <span class="metric-value">{{ $questions->total() }}</span>
                <span class="metric-caption">{{ request()->query() ? 'matching filters' : 'available in bank' }}</span>
            </div>
            @endif
        </div>

        <div class="search-filters-card">
            <form method="GET" action="{{ route('questionbank.search') }}" id="searchForm">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label for="search_term" class="filter-label">Keyword</label>
                        <input type="search"
                            name="search_term"
                            id="search_term"
                            class="filter-input"
                            value="{{ request('search_term') }}"
                            placeholder="Search by title, chapter, or keyword">
                    </div>
                    <div class="filter-field">
                        <label for="search_academic_level" class="filter-label">Academic Level</label>
                        <select name="academic_level" class="filter-select" id="search_academic_level">
                            <option value="">All Levels</option>
                            <option value="Form 4" {{ request('academic_level') == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                            <option value="Form 5" {{ request('academic_level') == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="search_chapter_select" class="filter-label">Chapter</label>
                        <select name="chapter" class="filter-select" id="search_chapter_select">
                            <option value="">All Chapters</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="search_difficulty" class="filter-label">Difficulty</label>
                        <select name="difficulty" class="filter-select" id="search_difficulty">
                            <option value="">All Difficulties</option>
                            <option value="Easy" {{ request('difficulty') == 'Easy' ? 'selected' : '' }}>Easy</option>
                            <option value="Intermediate" {{ request('difficulty') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="Advanced" {{ request('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-submit">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>
                    @if(request()->query())
                    <a href="{{ route('questionbank.search') }}" class="filter-reset">
                        <i class="fas fa-undo"></i>
                        Clear Filters
                    </a>
                    @endif
                </div>
            </form>
        </div>

        @if(isset($questions) && $questions->count() > 0)
        <div class="search-results">
            <div class="results-summary">
                Showing <strong>{{ $questions->firstItem() }}</strong>&ndash;<strong>{{ $questions->lastItem() }}</strong> of <strong>{{ $questions->total() }}</strong> results {{ request()->query() ? 'for your filters' : 'available right now' }}.
            </div>
            <div class="results-grid">
                @foreach($questions as $question)
                @php
                    $authorName = optional($question->user)->name ?? ($question->uploaded_by ?? null);
                @endphp
                <article class="result-card">
                    <div class="result-card-media">
                        @if($question->question_image)
                        <img src="{{ asset('storage/' . $question->question_image) }}"
                            alt="Question image"
                            onclick="viewQuestion({{ $question->id }})"
                            onerror="this.remove(); const placeholder = document.createElement('div'); placeholder.className='result-card-placeholder'; placeholder.innerHTML='<i class=&quot;fas fa-image&quot;></i><span>No image</span>'; this.closest('.result-card-media').appendChild(placeholder);">
                        @else
                        <div class="result-card-placeholder">
                            <i class="fas fa-image"></i>
                            <span>No image</span>
                        </div>
                        @endif
                    </div>
                    <div class="result-card-body">
                        <div class="result-card-meta">
                            @if($question->academic_level)
                            <span class="meta-chip">{{ $question->academic_level }}</span>
                            @endif
                            @if($question->difficulty)
                            <span class="difficulty-badge difficulty-{{ strtolower($question->difficulty) }}">{{ $question->difficulty }}</span>
                            @endif
                        </div>
                        <h3 class="result-card-title">{{ $question->chapter }}</h3>
                        <dl class="result-card-details">
                            <div>
                                <dt>ID</dt>
                                <dd>#{{ $question->id }}</dd>
                            </div>
                            <div>
                                <dt>Uploaded</dt>
                                <dd>{{ $question->created_at ? $question->created_at->format('M d, Y') : 'N/A' }}</dd>
                            </div>
                            @if($authorName)
                            <div>
                                <dt>Shared by</dt>
                                <dd>{{ $authorName }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                    <div class="result-card-footer">
                        <button type="button" class="result-card-action" onclick="viewQuestion({{ $question->id }})">
                            <i class="fas fa-eye"></i>
                            View Question
                        </button>
                    </div>
                </article>
                @endforeach
            </div>

        </div>
        </div>

        <!-- ADD THE CUSTOM PAGINATION HERE -->
        @if(isset($questions) && method_exists($questions, 'hasPages') && $questions->hasPages())
        <div class="custom-pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px;">
            {{-- Previous Page Link --}}
            @if ($questions->onFirstPage())
                <span style="padding: 8px 12px; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">‹ Previous</span>
            @else
                <a href="{{ $questions->appends(request()->query())->previousPageUrl() }}" 
                   style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
                   onmouseover="this.style.background='#f1f5f9'" 
                   onmouseout="this.style.background='white'">‹ Previous</a>
            @endif

            {{-- Page Numbers --}}
            @foreach(range(1, $questions->lastPage()) as $page)
                @if($page == $questions->currentPage())
                    <span style="padding: 8px 12px; background: #2563eb; color: white; border: 1px solid #2563eb; border-radius: 6px; font-size: 14px; font-weight: 600;">{{ $page }}</span>
                @else
                    <a href="{{ $questions->appends(request()->query())->url($page) }}" 
                       style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
                       onmouseover="this.style.background='#f1f5f9'" 
                       onmouseout="this.style.background='white'">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($questions->hasMorePages())
                <a href="{{ $questions->appends(request()->query())->nextPageUrl() }}" 
                   style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
                   onmouseover="this.style.background='#f1f5f9'" 
                   onmouseout="this.style.background='white'">Next ›</a>
            @else
                <span style="padding: 8px 12px; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">Next ›</span>
            @endif
        </div>

        <div style="text-align: center; margin-top: 10px; font-size: 14px; color: #6b7280;">
            Page {{ $questions->currentPage() }} of {{ $questions->lastPage() }}
        </div>
        @endif
        
        @elseif(request()->query())
        <div class="search-empty-state">
            <i class="fas fa-search"></i>
            <p>No questions match your current filters.</p>
            <a href="{{ route('questionbank.search') }}" class="filter-reset">
                <i class="fas fa-undo"></i>
                Clear Filters
            </a>
        </div>
        @else
        <div class="search-empty-state">
            <i class="fas fa-search"></i>
            <p>Use the filters above to start exploring the question bank.</p>
        </div>
        @endif
    </div>
    @endif


    @if($isCreate)
    <!-- Create Question Form -->
         <div class="fixed-back-button">
        <a href="{{ route('questionbank.submit-options') }}" class="btn btn-outline-primary">
            &larr; Back
        </a>
    </div>
    <div class="enhanced-form-container"> <!-- Use enhanced-form-container for consistency with edit page -->
        <div class="page-header-section">
            <div class="header-title">
                <i class="fas fa-plus"></i>
                Create New Question
            </div>
        </div>

        <form method="POST" action="{{ route('questionbank.store') }}" enctype="multipart/form-data" id="enhancedCreateForm">
            @csrf

            <!-- Basic Information Section -->
            <div class="form-row">
                <div class="form-group">
                    <label for="academic_level">Academic Level:</label>
                    <select name="academic_level" class="form-select enhanced-select" required>
                        <option value="">Select Level</option>
                        <option value="Form 4" {{ old('academic_level') == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                        <option value="Form 5" {{ old('academic_level') == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                    </select>
                    @error('academic_level')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="chapter">Chapter:</label>
                    <select name="chapter" id="chapter_select" class="form-select enhanced-select" required>
                        <option value="">Select Academic Level First</option>
                    </select>
                    @error('chapter')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="difficulty">Difficulty:</label>
                    <select name="difficulty" class="form-select enhanced-select" required>
                        <option value="">Select Difficulty</option>
                        <option value="Easy" {{ old('difficulty') == 'Easy' ? 'selected' : '' }}>easy</option>
                        <option value="Intermediate" {{ old('difficulty') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ old('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    @error('difficulty')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Upload Section -->
            <div class="visual-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-question"></i>
                    </div>
                    <span>Question</span>
                </div>

                <!-- Custom Upload Box (clickable) -->
                <label for="question_image" class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 30px;"></i>
                    </div>
                    <div class="upload-text">Click to upload question image</div>
                    
    <div class="image-preview" id="question_image_preview"></div>
                    <input type="file" id="question_image" name="question_image" accept="image/*" class="hidden-input" required>

                </label>

                <!-- Hidden File Input -->
                <!-- Laravel Validation Error -->
                @error('question_image')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>



            <!-- Answer Section -->
            <div class="visual-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Answer:</span>
                </div>
                <div class="answer-options">
                    <label class="answer-option" for="answer_a">
                        <input type="radio" id="answer_a" name="answer_image" value="A" {{ old('answer_image') == 'A' ? 'checked' : '' }}>
                        <span>A</span>
                    </label>
                    <label class="answer-option" for="answer_b">
                        <input type="radio" id="answer_b" name="answer_image" value="B" {{ old('answer_image') == 'B' ? 'checked' : '' }}>
                        <span>B</span>
                    </label>
                    <label class="answer-option" for="answer_c">
                        <input type="radio" id="answer_c" name="answer_image" value="C" {{ old('answer_image') == 'C' ? 'checked' : '' }}>
                        <span>C</span>
                    </label>
                    <label class="answer-option" for="answer_d">
                        <input type="radio" id="answer_d" name="answer_image" value="D" {{ old('answer_image') == 'D' ? 'checked' : '' }}>
                        <span>D</span>
                    </label>
                </div>
                @error('answer_image')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tip 1 (Easy Level) -->
            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Tip 1 (Easy Level)
                </div>
                <div class="tip-content">
                                        <label for="tip_easy" class="tip-upload-area" id="tipEasyUpload">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload tip image (Optional)</div>
                        <div class="tip-preview" id="tip_easy_preview"></div>
                                            <input type="file" id="tip_easy" name="tip_easy" accept="image/*" class="hidden-input">

                    </label>
                    @error('tip_easy')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Tip 2 (Intermediate Level) -->
            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Tip 2 (Intermediate Level)
                </div>
                <div class="tip-content">
                    <div class="tip-upload-area locked" id="tipIntermediateUpload" title="Please upload Tip 1 first">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload tip image (Optional: Please upload tip 1 first)</div>
                        <div class="tip-preview" id="tip_intermediate_preview"></div>
                        <input type="file" id="tip_intermediate" name="tip_intermediate" accept="image/*" class="hidden-input">
                    </div>
                    @error('tip_intermediate')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Tip 3 (Advanced Level) -->
            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Tip 3 (Advanced Level)
                </div>
                <div class="tip-content">
                    <div class="tip-upload-area locked" id="tipAdvancedUpload" title="Please upload Tip 2 first">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload tip image (Optional: Please upload tip 2 first)</div>
                        <div class="tip-preview" id="tip_advanced_preview"></div>
                        <input type="file" id="tip_advanced" name="tip_advanced" accept="image/*" class="hidden-input">
                    </div>
                    @error('tip_advanced')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Centered Create Question Button -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" id="submit_btn" class="submit-button">
                    <i class="fas fa-save"></i> Create Question
                </button>
            </div>
        </form>
    </div>
    @endif

    @if($isEdit)
    <!-- Edit Question Form -->
    <div class="enhanced-form-container">
        <div class="page-header-section">
            <div class="header-title">
                <i class="fas fa-edit"></i>
                Edit Question
            </div>
            <a href="{{ route('questionbank.user-questions') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <form method="POST" action="{{ route('questionbank.update', $question->id) }}" enctype="multipart/form-data" id="editQuestionForm">
            @csrf
            @method('PUT')

            <!-- Basic Information Section -->
            <div class="form-row">
                <div class="form-group">
                    <label for="academic_level">Academic Level:</label>
                    <select name="academic_level" class="enhanced-select" required>
                        <option value="">Select Level</option>
                        <option value="Form 4" {{ old('academic_level', $question->academic_level) == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                        <option value="Form 5" {{ old('academic_level', $question->academic_level) == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                    </select>
                    @error('academic_level')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="chapter">Chapter:</label>
                    <select name="chapter" id="edit_chapter_select" class="enhanced-select" required>
                        <option value="">Select Academic Level First</option>
                        <!-- JavaScript will populate this -->
                    </select>
                    @error('chapter')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="difficulty">Difficulty:</label>
                    <select name="difficulty" class="enhanced-select" required>
                        <option value="">Select Difficulty</option>
                        <option value="Easy" {{ old('difficulty', $question->difficulty) == 'Easy' ? 'selected' : '' }}>Easy</option>
                        <option value="Intermediate" {{ old('difficulty', $question->difficulty) == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ old('difficulty', $question->difficulty) == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    @error('difficulty')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Current Question Image -->
            @if($question->question_image)
            <div class="current-image-section">
                <h4>Current Question Image:</h4>
                <img src="{{ asset('storage/' . $question->question_image) }}" alt="Current Question" style="max-width: 300px; border-radius: 8px; margin-bottom: 15px;">
            </div>
            @endif

            <!-- Question Section -->
            <div class="visual-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-question"></i>
                    </div>
                    <span>Update Question Image (Optional)</span>
                </div>
                <div class="upload-area" onclick="document.getElementById('edit_question_image').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">Click to upload new question image</div>
                    <input type="file" id="edit_question_image" name="question_image" accept="image/*">
                </div>
                <div id="edit_question_preview"></div>
                @error('question_image')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Answer Section -->
            <div class="visual-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Answer:</span>
                </div>
                <div class="answer-options">
                    <label class="answer-option" for="edit_answer_a">
                        <input type="radio" id="edit_answer_a" name="answer_image" value="A" {{ old('answer_image', $question->answer_image) == 'A' ? 'checked' : '' }}>
                        <span>A</span>
                    </label>
                    <label class="answer-option" for="edit_answer_b">
                        <input type="radio" id="edit_answer_b" name="answer_image" value="B" {{ old('answer_image', $question->answer_image) == 'B' ? 'checked' : '' }}>
                        <span>B</span>
                    </label>
                    <label class="answer-option" for="edit_answer_c">
                        <input type="radio" id="edit_answer_c" name="answer_image" value="C" {{ old('answer_image', $question->answer_image) == 'C' ? 'checked' : '' }}>
                        <span>C</span>
                    </label>
                    <label class="answer-option" for="edit_answer_d">
                        <input type="radio" id="edit_answer_d" name="answer_image" value="D" {{ old('answer_image', $question->answer_image) == 'D' ? 'checked' : '' }}>
                        <span>D</span>
                    </label>
                </div>
                @error('answer_image')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Current Tips (if any) - FIXED VERSION -->
            @if($question->tip_easy || $question->tip_intermediate || $question->tip_advanced)
            <div class="current-image-section">
                <h4><i class="fas fa-lightbulb" style="color: #f59e0b; margin-right: 8px;"></i>Current Tips:</h4>
                <div class="current-tips-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">

                    @php
                    // Helper function to check if value is an image path
                    function isImagePath($value) {
                    return $value && (strpos($value, '/') !== false || strpos($value, '.') !== false) &&
                    !in_array(strtolower($value), ['thth', 'thrth', 'test', 'text']);
                    }
                    @endphp

                    @if(isImagePath($question->tip_easy))
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-lightbulb" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #166534; font-size: 14px;">Easy Tip</strong>
                        </div>
                        <img src="{{ asset('storage/' . $question->tip_easy) }}" alt="Easy Tip"
                            style="max-width: 100%; border-radius: 8px; margin-bottom: 15px; cursor: pointer;"
                            onclick="window.open('{{ asset('storage/' . $question->tip_easy) }}', '_blank')">
                    </div>
                    @elseif($question->tip_easy)
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #6b7280; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #6b7280; font-size: 14px;">Easy Tip (Text)</strong>
                        </div>
                        <div style="padding: 10px; background: #f3f4f6; border-radius: 6px; font-size: 12px; color: #6b7280;">
                            {{ $question->tip_easy }}
                        </div>
                    </div>
                    @endif

                    @if(isImagePath($question->tip_intermediate))
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-lightbulb" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #92400e; font-size: 14px;">Intermediate Tip</strong>
                        </div>
                        <img src="{{ asset('storage/' . $question->tip_intermediate) }}" alt="Intermediate Tip"
                            style="max-width: 100%; border-radius: 8px; margin-bottom: 15px; cursor: pointer;"
                            onclick="window.open('{{ asset('storage/' . $question->tip_intermediate) }}', '_blank')">
                    </div>
                    @elseif($question->tip_intermediate)
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #6b7280; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #6b7280; font-size: 14px;">Intermediate Tip (Text)</strong>
                        </div>
                        <div style="padding: 10px; background: #f3f4f6; border-radius: 6px; font-size: 12px; color: #6b7280;">
                            {{ $question->tip_intermediate }}
                        </div>
                    </div>
                    @endif

                    @if(isImagePath($question->tip_advanced))
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-lightbulb" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #991b1b; font-size: 14px;">Advanced Tip</strong>
                        </div>
                        <img src="{{ asset('storage/' . $question->tip_advanced) }}" alt="Advanced Tip"
                            style="max-width: 100%; border-radius: 8px; margin-bottom: 15px; cursor: pointer;"
                            onclick="window.open('{{ asset('storage/' . $question->tip_advanced) }}', '_blank')">
                    </div>
                    @elseif($question->tip_advanced)
                    <div class="tip-display-card">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <div style="width: 20px; height: 20px; background: #6b7280; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation" style="color: white; font-size: 10px;"></i>
                            </div>
                            <strong style="color: #6b7280; font-size: 14px;">Advanced Tip (Text)</strong>
                        </div>
                        <div style="padding: 10px; background: #f3f4f6; border-radius: 6px; font-size: 12px; color: #6b7280;">
                            {{ $question->tip_advanced }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            <!-- Tips Sections -->
            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Update Tip 1 (Easy Level) - Optional
                </div>
                <div class="tip-content">
                    <div class="tip-upload-area" onclick="document.getElementById('edit_tip_easy').click()">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload new easy tip image</div>
                        <input type="file" id="edit_tip_easy" name="tip_easy" accept="image/*">
                    </div>
                    <div id="edit_tip_easy_preview"></div>
                </div>
            </div>

            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Update Tip 2 (Intermediate Level)
                </div>
                <div class="tip-content">
                    <div class="tip-upload-area" onclick="document.getElementById('edit_tip_intermediate').click()">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload new intermediate tip image</div>
                        <input type="file" id="edit_tip_intermediate" name="tip_intermediate" accept="image/*">
                    </div>
                    <div id="edit_tip_intermediate_preview"></div>
                </div>
            </div>

            <div class="tip-section">
                <div class="tip-header">
                    <i class="fas fa-lightbulb"></i>
                    Update Tip 3 (Advanced Level) - Optional
                </div>
                <div class="tip-content">
                    <div class="tip-upload-area" onclick="document.getElementById('edit_tip_advanced').click()">
                        <div class="upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="upload-text">Click to upload new advanced tip image</div>
                        <input type="file" id="edit_tip_advanced" name="tip_advanced" accept="image/*">
                    </div>
                    <div id="edit_tip_advanced_preview"></div>
                </div>
            </div>

            <div class="form-actions" style="text-align: center; margin-top: 30px;">
                <button type="submit" class="submit-button">
                    <i class="fas fa-save"></i> Update Question
                </button>
                <a href="{{ route('questionbank.user-questions') }}" class="btn-cancel" style="margin-left: 15px; display: inline-flex; align-items: center; gap: 8px; padding: 18px 30px; background: #6b7280; color: white; text-decoration: none; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    @endif

    @if($isShow)
    <!-- Show Question Details - Add your show view here if needed -->
    @endif

    @if($isUserQuestions)
    <!-- User Questions List -->
    <div class="fixed-back-button">
        <a href="{{ route('questionbank.submit-options') }}" class="btn btn-outline-primary">
            &larr; Back
        </a>
    </div>
    <div class="user-questions-container">
        <div class="page-header-section">
            <div class="header-title">
                <i class="fas fa-list-alt"></i>
                My Submitted Questions
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('questionbank.user-questions') }}" id="filterForm">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Academic Level</label>
                        <select name="academic_level" class="filter-select">
                            <option value="">All Levels</option>
                            <option value="Form 4" {{ request('academic_level') == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                            <option value="Form 5" {{ request('academic_level') == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                        </select>
                    </div>

                    
                    <div class="filter-group">
                        <label>Chapter</label>
                        <select name="chapter" id="filter_chapter_select" class="filter-select">
                            <option value="">All Chapters</option>
                            @if(request('chapter'))
                            <option value="{{ request('chapter') }}" selected>{{ request('chapter') }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Difficulty</label>
                        <select name="difficulty" class="filter-select">
                            <option value="">All Difficulties</option>
                            <option value="Easy" {{ request('difficulty') == 'Easy' ? 'selected' : '' }}>Easy</option>
                            <option value="Intermediate" {{ request('difficulty') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="Advanced" {{ request('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>

                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    @if(request()->hasAny(['academic_level', 'chapter', 'difficulty']))
                    <a href="{{ route('questionbank.user-questions') }}" class="clear-btn">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Questions List -->
        @if(isset($questions) && method_exists($questions, 'count') && $questions->count() > 0)
        <div class="questions-grid">
            @foreach($questions as $question)
            <div class="question-card-user">
                <div class="question-header">
                    <div class="question-meta">
                        <span class="academic-level">{{ $question->academic_level }}</span>
                        <span class="difficulty-badge difficulty-{{ strtolower($question->difficulty) }}">
                            {{ $question->difficulty }}
                        </span>
                    </div>
                    <div class="question-actions">
                        <button class="action-btn edit-btn" onclick="editQuestion({{ $question->id }})" title="Edit Question">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteQuestion({{ $question->id }})" title="Delete Question">
                            <i class="fas fa-trash"></i>
                            <span>Delete</span>
                        </button>
                        <button class="action-btn view-btn" onclick="viewQuestion({{ $question->id }})" title="View Details">
                            <i class="fas fa-eye"></i>
                            <span>View</span>
                        </button>
                    </div>
                </div>

                <div class="question-content">
                    <div class="question-info">
                        <p class="chapter-info">{{ $question->chapter }}</p>
                        <div class="question-details">
                            @if($question->answer_image)
                            <span class="answer-info">Answer: {{ $question->answer_image }}</span>
                            @endif
                            <span class="upload-date">{{ $question->created_at ? $question->created_at->format('M d, Y') : '' }}</span>
                        </div>
                    </div>

                    @if($question->question_image)
                    <div class="question-thumbnail">
                        <img src="{{ asset('storage/' . $question->question_image) }}"
                            alt="Question Image"
                            onclick="viewQuestion({{ $question->id }})"
                            onerror="this.style.display='none'; console.log('Image failed to load: {{ $question->question_image }}');">
                    </div>
                    @else
                    <div class="question-thumbnail no-image">
                        <i class="fas fa-image" style="font-size: 24px; color: #9ca3af;"></i>
                    </div>
                    @endif
                </div>

                <div class="question-footer">
                    <div class="tip-indicators">
                        @if($question->tip_easy)
                        <span class="tip-indicator tip-easy" title="Has Easy Tip">
                            <i class="fas fa-lightbulb"></i> E
                        </span>
                        @endif
                        @if($question->tip_intermediate)
                        <span class="tip-indicator tip-intermediate" title="Has Intermediate Tip">
                            <i class="fas fa-lightbulb"></i> I
                        </span>
                        @endif
                        @if($question->tip_advanced)
                        <span class="tip-indicator tip-advanced" title="Has Advanced Tip">
                            <i class="fas fa-lightbulb"></i> A
                        </span>
                        @endif
                    </div>
                    <small class="question-id">ID: {{ $question->id }}</small>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($questions->hasPages())
<div class="custom-pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px;">
    {{-- Previous Page Link --}}
    @if ($questions->onFirstPage())
        <span style="padding: 8px 12px; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">‹ Previous</span>
    @else
        <a href="{{ $questions->appends(request()->query())->previousPageUrl() }}" 
           style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
           onmouseover="this.style.background='#f1f5f9'" 
           onmouseout="this.style.background='white'">‹ Previous</a>
    @endif

    {{-- Page Numbers --}}
    @foreach(range(1, $questions->lastPage()) as $page)
        @if($page == $questions->currentPage())
            <span style="padding: 8px 12px; background: #2563eb; color: white; border: 1px solid #2563eb; border-radius: 6px; font-size: 14px; font-weight: 600;">{{ $page }}</span>
        @else
            <a href="{{ $questions->appends(request()->query())->url($page) }}" 
               style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
               onmouseover="this.style.background='#f1f5f9'" 
               onmouseout="this.style.background='white'">{{ $page }}</a>
        @endif
    @endforeach

    {{-- Next Page Link --}}
    @if ($questions->hasMorePages())
        <a href="{{ $questions->appends(request()->query())->nextPageUrl() }}" 
           style="padding: 8px 12px; color: #475569; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 14px; transition: all 0.2s;"
           onmouseover="this.style.background='#f1f5f9'" 
           onmouseout="this.style.background='white'">Next ›</a>
    @else
        <span style="padding: 8px 12px; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 14px;">Next ›</span>
    @endif
</div>

<div style="text-align: center; margin-top: 10px; font-size: 14px; color: #6b7280;">
    Showing {{ $questions->firstItem() }}-{{ $questions->lastItem() }} of {{ $questions->total() }} results
</div>
@endif
        @else
        <div class="no-questions-container">
            <div class="no-questions-content">
                <i class="fas fa-question-circle"></i>
                <h3>No Questions Found</h3>
                <p>You haven't submitted any questions yet, or no questions match your current filters.</p>
                <a href="{{ route('questionbank.create') }}" class="create-first-btn">
                    <i class="fas fa-plus"></i> Create Your First Question
                </a>
            </div>
        </div>
        @endif
    </div>

    @endif
    <!-- Question Detail Modal -->
    <div id="questionModal" class="question-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Question Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="question-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this question? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                    <button class="btn-delete" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function editQuestion(questionId) {
            window.location.href = `/questionbank/${questionId}/edit`;
        }

        function viewQuestion(questionId) {
            const modal = document.getElementById('questionModal');
            const modalBody = document.getElementById('modalBody');

            if (!modal || !modalBody) {
                console.error('Modal elements not found');
                return;
            }

            modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            modal.style.display = 'flex';

            // Fetch question details
            fetch(`/questionbank/${questionId}/info`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const question = data.question;

                        modalBody.innerHTML = `
                    <div class="question-detail">
                        <div class="detail-row">
                            <strong>Academic Level:</strong> ${question.academic_level}
                        </div>
                        <div class="detail-row">
                            <strong>Chapter:</strong> ${question.chapter}
                        </div>
                        <div class="detail-row">
                            <strong>Difficulty:</strong> 
                            <span class="difficulty-badge difficulty-${question.difficulty.toLowerCase()}">${question.difficulty}</span>
                        </div>
                        ${question.answer_image ? `<div class="detail-row"><strong>Answer:</strong> ${question.answer_image}</div>` : ''}
                        ${question.question_image ? `
                            <div class="detail-row">
                                <strong>Question Image:</strong><br>
                                <img src="${question.question_image}" alt="Question" 
                                     style="max-width: 100%; border-radius: 8px; margin-top: 10px; cursor: pointer;" 
                                     onclick="window.open('${question.question_image}', '_blank')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; color: #ef4444; text-align: center; padding: 20px;">
                                    <i class="fas fa-exclamation-triangle"></i> Image could not be loaded
                                </div>
                            </div>
                        ` : ''}
                        ${question.tip_easy ? `
                            <div class="detail-row">
                                <strong>Easy Tip:</strong><br>
                                <img src="/storage/${question.tip_easy}" alt="Easy Tip" 
                                     style="max-width: 100%; border-radius: 8px; margin-top: 10px; cursor: pointer;" 
                                     onclick="window.open('/storage/${question.tip_easy}', '_blank')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; color: #ef4444; text-align: center; padding: 10px;">
                                    <i class="fas fa-exclamation-triangle"></i> Tip image could not be loaded
                                </div>
                            </div>
                        ` : ''}
                        ${question.tip_intermediate ? `
                            <div class="detail-row">
                                <strong>Intermediate Tip:</strong><br>
                                <img src="/storage/${question.tip_intermediate}" alt="Intermediate Tip" 
                                     style="max-width: 100%; border-radius: 8px; margin-top: 10px; cursor: pointer;" 
                                     onclick="window.open('/storage/${question.tip_intermediate}', '_blank')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; color: #ef4444; text-align: center; padding: 10px;">
                                    <i class="fas fa-exclamation-triangle"></i> Tip image could not be loaded
                                </div>
                            </div>
                        ` : ''}
                        ${question.tip_advanced ? `
                            <div class="detail-row">
                                <strong>Advanced Tip:</strong><br>
                                <img src="/storage/${question.tip_advanced}" alt="Advanced Tip" 
                                     style="max-width: 100%; border-radius: 8px; margin-top: 10px; cursor: pointer;" 
                                     onclick="window.open('/storage/${question.tip_advanced}', '_blank')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div style="display: none; color: #ef4444; text-align: center; padding: 10px;">
                                    <i class="fas fa-exclamation-triangle"></i> Tip image could not be loaded
                                </div>
                            </div>
                        ` : ''}
                        <div class="detail-row">
                            <strong>Created:</strong> ${question.created_at || 'N/A'}
                        </div>
                        <div class="detail-row">
                            <strong>Created by:</strong> ${question.user_name || 'Unknown'}
                        </div>
                    </div>
                `;
                    } else {
                        modalBody.innerHTML = '<div class="error-message">Failed to load question details.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching question details:', error);
                    modalBody.innerHTML = '<div class="error-message">Error loading question details.</div>';
                });
        }

        function deleteQuestion(questionId) {
            const confirmModal = document.getElementById('confirmModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            if (!confirmModal || !confirmBtn) {
                console.error('Confirm modal elements not found');
                return;
            }

            confirmModal.style.display = 'flex';

            confirmBtn.onclick = function() {
                // Show immediate loading state
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                this.disabled = true;

                // Close modal immediately to show action is processing
                setTimeout(() => {
                    confirmModal.style.display = 'none';
                }, 500);

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('CSRF token not found. Please refresh the page.');
                    confirmModal.style.display = 'flex';
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                    return;
                }

                // Perform delete with optimistic UI update
                const questionCard = document.querySelector(`[onclick*="${questionId}"]`).closest('.question-card-user');
                if (questionCard) {
                    questionCard.style.opacity = '0.5';
                    questionCard.style.pointerEvents = 'none';
                }

                fetch(`/questionbank/${questionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Smooth removal animation
                            if (questionCard) {
                                questionCard.style.transform = 'scale(0)';
                                questionCard.style.transition = 'all 0.3s ease';
                                setTimeout(() => {
                                    location.reload();
                                }, 300);
                            } else {
                                location.reload();
                            }
                        } else {
                            // Restore card if delete failed
                            if (questionCard) {
                                questionCard.style.opacity = '1';
                                questionCard.style.pointerEvents = 'auto';
                            }
                            alert('Failed to delete question: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        // Restore card if delete failed
                        if (questionCard) {
                            questionCard.style.opacity = '1';
                            questionCard.style.pointerEvents = 'auto';
                        }
                        console.error('Delete error:', error);
                        alert('Error deleting question. Please try again.');
                    });
            };
        }

        function closeModal() {
            document.getElementById('questionModal').style.display = 'none';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('question-modal')) {
                e.target.style.display = 'none';
            }
        });

        // Add CSS for detail rows
        const additionalCSS = `
.detail-row {
    margin-bottom: 15px;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
    border-bottom: none;
}

.error-message {
    color: #ef4444;
    text-align: center;
    padding: 20px;
}
`;

        // Inject additional CSS
        const styleSheet = document.createElement("style");
        styleSheet.type = "text/css";
        styleSheet.innerText = additionalCSS;
        document.head.appendChild(styleSheet);
    </script>

</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Chapter data for both forms
    const chapterData = {
        'Form 4': [
            'Chapter 1: Algebra & Algebraic Manipulation',
            'Chapter 2: Relations, Functions & Graphs',
            'Chapter 3: Geometry',
            'Chapter 4: Trigonometry',
            'Chapter 5: Mensuration',
            'Chapter 6: Statistics',
            'Chapter 7: Probability',
            'Chapter 8: Consumer Arithmetic',
            'Chapter 9: Vectors',
            'Chapter 10: Matrices',
            'Chapter 11: Set Theory'
        ],
        'Form 5': [
            'Chapter 1: Advanced Algebra & Algebraic Manipulation',
            'Chapter 2: Advanced Relations, Functions & Graphs',
            'Chapter 3: Advanced Geometry',
            'Chapter 4: Advanced Trigonometry',
            'Chapter 5: Advanced Mensuration',
            'Chapter 6: Advanced Statistics',
            'Chapter 7: Advanced Probability',
            'Chapter 8: Advanced Consumer Arithmetic',
            'Chapter 9: Advanced Vectors',
            'Chapter 10: Advanced Matrices',
            'Chapter 11: Advanced Set Theory'
        ]
    };

    // Function to update chapters based on academic level
    function updateChapters(academicLevelSelect, chapterSelect, selectedChapter = '') {
        const selectedLevel = academicLevelSelect.value;
        const chapters = chapterData[selectedLevel] || [];

        chapterSelect.innerHTML = ''; // Clear existing options

        if (chapters.length === 0) {
            chapterSelect.innerHTML = '<option value="">Select Academic Level First</option>';
            chapterSelect.disabled = true;
            chapterSelect.classList.add('chapter-loading'); // Add loading class for visual hint
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Chapter';
            chapterSelect.appendChild(defaultOption);

            chapters.forEach(chapter => {
                const option = document.createElement('option');
                option.value = chapter;
                option.textContent = chapter;
                option.selected = selectedChapter === chapter;
                chapterSelect.appendChild(option);
            });

            chapterSelect.disabled = false;
            chapterSelect.classList.remove('chapter-loading');
        }
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);

    document.addEventListener('DOMContentLoaded', function() {
        // --- DYNAMIC CHAPTER LOADING (Create Form) ---
        const createAcademicSelect = document.querySelector('#enhancedCreateForm select[name="academic_level"]');
        const createChapterSelect = document.getElementById('chapter_select');
        

        if (createAcademicSelect && createChapterSelect) {
            createAcademicSelect.addEventListener('change', function() {
                updateChapters(this, createChapterSelect);
            });
            // Initial call for create form, preserving old input if any
            updateChapters(createAcademicSelect, createChapterSelect, '{{ old("chapter") }}');
        }

        // --- DYNAMIC CHAPTER LOADING (Edit Form) ---
        const editAcademicSelect = document.querySelector('#editQuestionForm select[name="academic_level"]');
        const editChapterSelect = document.getElementById('edit_chapter_select');

        if (editAcademicSelect && editChapterSelect) {
            editAcademicSelect.addEventListener('change', function() {
                updateChapters(this, editChapterSelect);
            });
            // Initial call for edit form, populating with existing data
            updateChapters(editAcademicSelect, editChapterSelect, '{{ isset($question) ? old("chapter", $question->chapter) : old("chapter") }}');
        }

        // --- FILE UPLOAD HANDLING ---
        function setupFileUpload(inputId, previewContainerId, uploadAreaClass, initialFilePath = null) {
            const input = document.getElementById(inputId);
            const previewContainer = document.getElementById(previewContainerId);
            const uploadArea = input ? input.closest('.' + uploadAreaClass) : null;
            const uploadText = uploadArea ? uploadArea.querySelector('.upload-text') : null;
            const uploadIcon = uploadArea ? uploadArea.querySelector('.upload-icon i') : null;

            if (!input || !previewContainer || !uploadArea || !uploadText || !uploadIcon) {
                console.warn(`Missing elements for file upload: ${inputId}`);
                return;
            }

            // Function to render the preview
            const renderPreview = (fileOrPath) => {
                previewContainer.innerHTML = ''; // Clear previous preview

                if (fileOrPath) {
                    let src;
                    if (typeof fileOrPath === 'string') { // It's a path
                        src = fileOrPath;
                    } else { // It's a File object
                        src = URL.createObjectURL(fileOrPath);
                    }
                    const img = document.createElement('img');
                    img.src = src;
                    img.className = 'image-preview'; // Use your existing class
                    img.alt = 'Image Preview';
                    previewContainer.appendChild(img);

                    uploadArea.classList.add('has-file');
                    uploadIcon.classList.remove('fa-cloud-upload-alt', 'fa-image');
                    uploadIcon.classList.add('fa-check-circle');
                    uploadText.textContent = typeof fileOrPath === 'string' ? 'Existing file' : fileOrPath.name;
                } else {
                    uploadArea.classList.remove('has-file');
                    uploadIcon.classList.remove('fa-check-circle');
                    uploadIcon.classList.add(inputId.includes('question') ? 'fa-cloud-upload-alt' : 'fa-image');
                    uploadText.textContent = inputId.includes('tip') ? 'Click to upload tip image (Optional)' : 'Click to upload new question image';
                }
                // Revoke URL if it was created from a File object
                if (typeof fileOrPath !== 'string' && fileOrPath) {
                    img.onload = () => URL.revokeObjectURL(img.src);
                }
            };

            // Initial rendering if there's an existing file
            if (initialFilePath) {
                renderPreview(initialFilePath);
            }

            // Handle actual file selection
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                renderPreview(file);
                // Trigger logic for progressive tips if this is a tip input
                if (inputId.includes('tip_easy') || inputId.includes('tip_intermediate')) {
                    checkTipUnlockConditions();
                }
            });

            // Make the entire upload area clickable
            if (uploadArea && !inputId.includes('tip_')) {
                uploadArea.addEventListener('click', () => input.click());
            }
        }

        // --- PROGRESSIVE TIP UPLOAD LOGIC ---
        const tipEasyInput = document.getElementById('tip_easy');
        const tipIntermediateInput = document.getElementById('tip_intermediate');
        const tipAdvancedInput = document.getElementById('tip_advanced');

        const tipIntermediateUploadArea = document.getElementById('tipIntermediateUpload');
        const tipAdvancedUploadArea = document.getElementById('tipAdvancedUpload');

        // Function to check and update unlock conditions for tips
        function checkTipUnlockConditions() {
            // Check Tip 1
            const hasTipEasy = tipEasyInput && (tipEasyInput.files.length > 0 || tipEasyInput.dataset.hasExistingFile === 'true');

            // Unlock/Lock Tip 2 based on Tip 1
            if (tipIntermediateUploadArea) {
                if (hasTipEasy) {
                    tipIntermediateUploadArea.classList.remove('locked');
                    tipIntermediateUploadArea.removeAttribute('title');
                    tipIntermediateInput.disabled = false;
                } else {
                    tipIntermediateUploadArea.classList.add('locked');
                    tipIntermediateUploadArea.setAttribute('title', 'Please upload Tip 1 first');
                    tipIntermediateInput.disabled = true;
                    // Clear intermediate tip if easy tip is removed
                    if (tipIntermediateInput.files.length > 0 || tipIntermediateInput.dataset.hasExistingFile === 'true') {
                        tipIntermediateInput.value = null; // Clear file input
                        tipIntermediateInput.dataset.hasExistingFile = 'false'; // Update state
                        document.getElementById('tip_intermediate_preview').innerHTML = ''; // Clear preview
                        tipIntermediateUploadArea.classList.remove('has-file');
                        tipIntermediateUploadArea.querySelector('.upload-text').textContent = 'Click to upload tip image (Optional: Please upload tip 1 first)';
                        tipIntermediateUploadArea.querySelector('.upload-icon i').className = 'fas fa-image';
                    }
                }
            }

            // Check Tip 2
            const hasTipIntermediate = tipIntermediateInput && (tipIntermediateInput.files.length > 0 || tipIntermediateInput.dataset.hasExistingFile === 'true');

            // Unlock/Lock Tip 3 based on Tip 2
            if (tipAdvancedUploadArea) {
                if (hasTipIntermediate) {
                    tipAdvancedUploadArea.classList.remove('locked');
                    tipAdvancedUploadArea.removeAttribute('title');
                    tipAdvancedInput.disabled = false;
                } else {
                    tipAdvancedUploadArea.classList.add('locked');
                    tipAdvancedUploadArea.setAttribute('title', 'Please upload Tip 2 first');
                    tipAdvancedInput.disabled = true;
                    // Clear advanced tip if intermediate tip is removed
                    if (tipAdvancedInput.files.length > 0 || tipAdvancedInput.dataset.hasExistingFile === 'true') {
                        tipAdvancedInput.value = null; // Clear file input
                        tipAdvancedInput.dataset.hasExistingFile = 'false'; // Update state
                        document.getElementById('tip_advanced_preview').innerHTML = ''; // Clear preview
                        tipAdvancedUploadArea.classList.remove('has-file');
                        tipAdvancedUploadArea.querySelector('.upload-text').textContent = 'Click to upload tip image (Optional: Please upload tip 2 first)';
                        tipAdvancedUploadArea.querySelector('.upload-icon i').className = 'fas fa-image';
                    }
                }
            }
        }

        // --- INITIAL FILE UPLOAD SETUP (for Create & Edit forms) ---
        // For Create Form
        if (document.getElementById('enhancedCreateForm')) {
            setupFileUpload('question_image', 'question_image_preview', 'upload-area');            setupFileUpload('tip_easy', 'tip_easy_preview', 'tip-upload-area');
            setupFileUpload('tip_intermediate', 'tip_intermediate_preview', 'tip-upload-area');
            setupFileUpload('tip_advanced', 'tip_advanced_preview', 'tip-upload-area');

            // Add change listeners for tips to trigger unlock logic
            if (tipEasyInput) tipEasyInput.addEventListener('change', checkTipUnlockConditions);
            if (tipIntermediateInput) tipIntermediateInput.addEventListener('change', checkTipUnlockConditions);

            checkTipUnlockConditions(); // Initial check on load
        }

        // For Edit Form
        if (document.getElementById('editQuestionForm')) {
            // Check if $question object is available (it should be on edit page)
            const questionData = @json(isset($question) ? $question : null);

            setupFileUpload('edit_question_image', 'edit_question_preview', 'upload-area',
                questionData && questionData.question_image ? `/storage/${questionData.question_image}` : null
            );

            // Set initial dataset for existing tips on edit page
            const editTipEasyInput = document.getElementById('edit_tip_easy');
            const editTipIntermediateInput = document.getElementById('edit_tip_intermediate');
            const editTipAdvancedInput = document.getElementById('edit_tip_advanced');

            if (editTipEasyInput && questionData && questionData.tip_easy) {
                editTipEasyInput.dataset.hasExistingFile = 'true';
                setupFileUpload('edit_tip_easy', 'edit_tip_easy_preview', 'tip-upload-area', `/storage/${questionData.tip_easy}`);
            } else if (editTipEasyInput) {
                editTipEasyInput.dataset.hasExistingFile = 'false';
                setupFileUpload('edit_tip_easy', 'edit_tip_easy_preview', 'tip-upload-area');
            }

            if (editTipIntermediateInput && questionData && questionData.tip_intermediate) {
                editTipIntermediateInput.dataset.hasExistingFile = 'true';
                setupFileUpload('edit_tip_intermediate', 'edit_tip_intermediate_preview', 'tip-upload-area', `/storage/${questionData.tip_intermediate}`);
            } else if (editTipIntermediateInput) {
                editTipIntermediateInput.dataset.hasExistingFile = 'false';
                setupFileUpload('edit_tip_intermediate', 'edit_tip_intermediate_preview', 'tip-upload-area');
            }

            if (editTipAdvancedInput && questionData && questionData.tip_advanced) {
                editTipAdvancedInput.dataset.hasExistingFile = 'true';
                setupFileUpload('edit_tip_advanced', 'edit_tip_advanced_preview', 'tip-upload-area', `/storage/${questionData.tip_advanced}`);
            } else if (editTipAdvancedInput) {
                editTipAdvancedInput.dataset.hasExistingFile = 'false';
                setupFileUpload('edit_tip_advanced', 'edit_tip_advanced_preview', 'tip-upload-area');
            }

            // Add change listeners for tips to trigger unlock logic (for edit form)
            if (editTipEasyInput) editTipEasyInput.addEventListener('change', checkTipUnlockConditions);
            if (editTipIntermediateInput) editTipIntermediateInput.addEventListener('change', checkTipUnlockConditions);

            // Re-run unlock conditions for existing files on edit page
            checkTipUnlockConditions();
        }

        // --- ANSWER SELECTION HANDLING ---
        document.querySelectorAll('input[name="answer_image"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.answer-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.closest('.answer-option').classList.add('selected');
            });
        });

        // Set initial selected state for any checked radio buttons on page load
        const checkedRadio = document.querySelector('input[name="answer_image"]:checked');
        if (checkedRadio) {
            checkedRadio.closest('.answer-option').classList.add('selected');
        }

        // --- FORM SUBMISSION LOADING STATE ---
        const createForm = document.getElementById('enhancedCreateForm');
        const editForm = document.getElementById('editQuestionForm');

        if (createForm) {
            createForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('#submit_btn');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                    submitBtn.disabled = true;
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.submit-button');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    submitBtn.disabled = true;
                }
            });
        }

        // --- FILTER FORM HANDLING (User Questions) ---
        const filterAcademicSelect = document.querySelector('.filter-section select[name="academic_level"]');
        const filterChapterSelect = document.getElementById('filter_chapter_select');

        if (filterAcademicSelect && filterChapterSelect) {
            filterAcademicSelect.addEventListener('change', function() {
                const selectedLevel = this.value;
                const chapters = chapterData[selectedLevel] || [];
                filterChapterSelect.innerHTML = ''; // Clear existing options

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'All Chapters';
                filterChapterSelect.appendChild(defaultOption);

                chapters.forEach(chapter => {
                    const option = document.createElement('option');
                    option.value = chapter;
                    option.textContent = chapter;
                    // Preserve selected filter chapter
                    if ('{{ request("chapter") }}' === chapter) {
                        option.selected = true;
                    }
                    filterChapterSelect.appendChild(option);
                });
            });

            // Trigger initial update for filter chapters based on current request
            filterAcademicSelect.dispatchEvent(new Event('change'));
        }

        const searchAcademicSelect = document.getElementById('search_academic_level');
        const searchChapterSelect = document.getElementById('search_chapter_select');
        let searchChapterPrefill = @json(request('chapter'));

        if (searchAcademicSelect && searchChapterSelect) {
            const populateSearchChapters = () => {
                const selectedLevel = searchAcademicSelect.value;
                const chapters = chapterData[selectedLevel] || [];

                searchChapterSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'All Chapters';
                searchChapterSelect.appendChild(defaultOption);

                chapters.forEach(chapter => {
                    const option = document.createElement('option');
                    option.value = chapter;
                    option.textContent = chapter;
                    if (searchChapterPrefill && chapter == searchChapterPrefill) {
                        option.selected = true;
                    }
                    searchChapterSelect.appendChild(option);
                });

                if (searchChapterPrefill && chapters.includes(searchChapterPrefill)) {
                    searchChapterSelect.value = searchChapterPrefill;
                    searchChapterPrefill = '';
                }
            };

            searchAcademicSelect.addEventListener('change', () => {
                searchChapterPrefill = '';
                populateSearchChapters();
            });

            populateSearchChapters();

            if (!searchAcademicSelect.value && searchChapterPrefill) {
                const fallbackOption = document.createElement('option');
                fallbackOption.value = searchChapterPrefill;
                fallbackOption.textContent = searchChapterPrefill;
                fallbackOption.selected = true;
                searchChapterSelect.appendChild(fallbackOption);
                searchChapterPrefill = '';
            }
        }

    });
</script>
@endpush
