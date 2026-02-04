<div>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <div class="hero-badge fade-in-up">
                        <i class="fas fa-chart-line"></i>
                        Analisis K-Means Clustering
                    </div>
                    <h1 class="hero-title fade-in-up delay-1">
                        Sistem Informasi <span>Gizi Balita</span>
                    </h1>
                    <p class="hero-description fade-in-up delay-2">
                        Monitor dan analisis status gizi balita secara efektif dengan teknologi K-Means Clustering untuk
                        penanganan stunting yang lebih tepat sasaran.
                    </p>
                    <div class="hero-buttons fade-in-up delay-3">
                        <a href="{{ route('login') }}" class="btn btn-primary-custom">
                            <i class="fas fa-sign-in-alt me-2"></i>Mulai Sekarang
                        </a>
                        <a href="#features" class="btn btn-outline-custom">
                            <i class="fas fa-info-circle me-2"></i>Pelajari Lebih Lanjut
                        </a>
                    </div>
                    <div class="hero-stats fade-in-up delay-4">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ number_format($totalBalita) }}</div>
                            <div class="hero-stat-label">Data Balita</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ $totalDesa }}</div>
                            <div class="hero-stat-label">Desa Tercakup</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">99%</div>
                            <div class="hero-stat-label">Akurasi</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-illustration fade-in-up delay-3">
                    <div class="hero-card">
                        <div class="hero-card-header">
                            <div class="hero-card-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div>
                                <div class="hero-card-title">Analisis Status Gizi</div>
                                <div class="hero-card-subtitle">Hasil Clustering Terbaru</div>
                            </div>
                        </div>
                        <div class="chart-placeholder">
                            <div class="chart-bar" style="height: 60%;"></div>
                            <div class="chart-bar" style="height: 80%;"></div>
                            <div class="chart-bar" style="height: 45%;"></div>
                            <div class="chart-bar" style="height: 90%;"></div>
                            <div class="chart-bar" style="height: 70%;"></div>
                            <div class="chart-bar" style="height: 55%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="width: 12px; height: 12px; background: var(--success-color); border-radius: 50%;"></span>
                                <small style="color: var(--text-secondary);">Gizi Baik</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="width: 12px; height: 12px; background: var(--warning-color); border-radius: 50%;"></span>
                                <small style="color: var(--text-secondary);">Gizi Kurang</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="width: 12px; height: 12px; background: var(--danger-color); border-radius: 50%;"></span>
                                <small style="color: var(--text-secondary);">Gizi Buruk</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-badge">
                    <i class="fas fa-star"></i>
                    Fitur Unggulan
                </div>
                <h2 class="section-title">Solusi Lengkap untuk Monitoring Gizi</h2>
                <p class="section-description">
                    SIGITA menyediakan berbagai fitur canggih untuk membantu petugas kesehatan dalam memantau dan
                    menganalisis status gizi balita.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon primary">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h3 class="feature-title">Manajemen Data Balita</h3>
                        <p class="feature-description">
                            Kelola data balita dengan mudah, termasuk informasi personal, riwayat pengukuran, dan status
                            gizi.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon success">
                            <i class="fas fa-ruler"></i>
                        </div>
                        <h3 class="feature-title">Pencatatan Pengukuran</h3>
                        <p class="feature-description">
                            Input data pengukuran berat dan tinggi badan secara berkala untuk memantau pertumbuhan
                            balita.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon warning">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3 class="feature-title">Analisis K-Means</h3>
                        <p class="feature-description">
                            Klasifikasi status gizi menggunakan algoritma K-Means Clustering untuk hasil yang akurat.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon danger">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="feature-title">Laporan & Visualisasi</h3>
                        <p class="feature-description">
                            Lihat hasil analisis dalam bentuk grafik dan laporan yang mudah dipahami untuk pengambilan
                            keputusan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-section" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-badge">
                    <i class="fas fa-cogs"></i>
                    Cara Kerja
                </div>
                <h2 class="section-title">Proses Analisis yang Mudah</h2>
                <p class="section-description">
                    Tiga langkah sederhana untuk menganalisis status gizi balita dengan teknologi K-Means Clustering.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-connector d-none d-md-block"></div>
                        <div class="step-number">1</div>
                        <h3 class="step-title">Input Data</h3>
                        <p class="step-description">
                            Masukkan data balita dan hasil pengukuran berat badan serta tinggi badan secara berkala.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-connector d-none d-md-block"></div>
                        <div class="step-number">2</div>
                        <h3 class="step-title">Proses Analisis</h3>
                        <p class="step-description">
                            Sistem akan memproses data menggunakan algoritma K-Means untuk mengklasifikasi status gizi.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3 class="step-title">Lihat Hasil</h3>
                        <p class="step-description">
                            Dapatkan hasil klasifikasi dalam bentuk visual yang mudah dipahami untuk tindak lanjut.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content text-center">
                <h2 class="cta-title">Mulai Gunakan SIGITA Sekarang</h2>
                <p class="cta-description">
                    Bergabung dengan petugas kesehatan lainnya dalam memantau status gizi balita secara efektif.
                </p>
                <a href="{{ route('login') }}" class="btn btn-white">
                    <i class="fas fa-arrow-right me-2"></i>Masuk ke Sistem
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <i class="fas fa-heartbeat me-2"></i>SIGITA
                    </div>
                    <p class="footer-description">
                        Sistem Informasi Gizi Balita untuk monitoring dan analisis status gizi menggunakan metode
                        K-Means Clustering.
                    </p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h4 class="footer-title">Menu</h4>
                    <ul class="footer-links">
                        <li><a href="#features">Fitur</a></li>
                        <li><a href="#how-it-works">Cara Kerja</a></li>
                        <li><a href="{{ route('login') }}">Masuk</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h4 class="footer-title">Fitur</h4>
                    <ul class="footer-links">
                        <li><a href="#">Data Balita</a></li>
                        <li><a href="#">Pengukuran</a></li>
                        <li><a href="#">Analisis K-Means</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h4 class="footer-title">Kontak</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope me-2"></i>admin@sigita.id</li>
                        <li><i class="fas fa-phone me-2"></i>+62 123 456 7890</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>Kolaka, Sulawesi Tenggara</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 SIGITA. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 80px;
            background: var(--bg-primary);
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 150%;
            background: radial-gradient(circle, rgba(32, 190, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(32, 190, 255, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .hero-title span {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .hero-stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .hero-illustration {
            position: relative;
            z-index: 1;
        }

        .hero-card {
            background: var(--bg-secondary);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border-color);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform 0.5s ease;
        }

        .hero-card:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }

        .hero-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .hero-card-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .hero-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .hero-card-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .chart-placeholder {
            height: 200px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding: 1rem;
        }

        .chart-bar {
            width: 30px;
            background: linear-gradient(to top, var(--gradient-start), var(--gradient-end));
            border-radius: 6px 6px 0 0;
            animation: grow 1s ease-out forwards;
        }

        @keyframes grow {
            from {
                height: 0;
            }
        }

        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background: var(--bg-secondary);
        }

        .feature-card {
            background: var(--bg-primary);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon.primary {
            background: rgba(32, 190, 255, 0.15);
            color: var(--primary-color);
        }

        .feature-icon.success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
        }

        .feature-icon.warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning-color);
        }

        .feature-icon.danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* How It Works */
        .how-section {
            padding: 6rem 0;
            background: var(--bg-primary);
        }

        .step-card {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1.5rem;
            position: relative;
            z-index: 1;
        }

        .step-connector {
            position: absolute;
            top: 40px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: var(--border-color);
        }

        .step-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .step-description {
            color: var(--text-secondary);
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .cta-description {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        .btn-white {
            background: white;
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                gap: 2rem;
            }

            .hero-illustration {
                margin-top: 3rem;
            }

            .hero-card {
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding-top: 100px;
                padding-bottom: 3rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .step-connector {
                display: none;
            }
        }
    </style>
</div>