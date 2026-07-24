<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniBill | Enterprise-Grade SaaS Billing Platform</title>
    <meta name="description" content="OmniBill is a high-performance, strictly isolated, multi-tenant billing engine designed for complex recurring revenue models.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0f172a;
            --bg-surface: rgba(30, 41, 59, 0.7);
            --bg-card: rgba(30, 41, 59, 0.5);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --border-subtle: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.15), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(139, 92, 246, 0.15), transparent 25%);
            background-attachment: fixed;
        }

        /* Nav */
        nav {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #60a5fa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        /* Hero */
        .hero {
            padding: 6rem 2rem 4rem;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: fadeInDown 0.8s ease backwards;
        }

        h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
            animation: fadeInUp 0.8s ease 0.1s backwards;
        }

        h1 span {
            background: linear-gradient(to right, #60a5fa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto 3rem;
            font-weight: 400;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }

        .cta-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            animation: fadeInUp 0.8s ease 0.3s backwards;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            box-shadow: 0 4px 14px 0 var(--accent-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.6);
        }

        .btn-secondary {
            background: var(--bg-surface);
            color: var(--text-primary);
            border-color: var(--border-subtle);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Features */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 2rem auto 6rem;
            padding: 0 2rem;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 1rem;
            padding: 2rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: #60a5fa;
        }

        .feature-icon svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .feature-card p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .cta-group {
                flex-direction: column;
            }
            .hero {
                padding: 4rem 1rem 2rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            OmniBill
        </div>
        <div class="nav-links">
            <a href="https://github.com/nishant-dd-24/omnibill">GitHub</a>
        </div>
    </nav>

    <main class="hero">
        <div class="badge">Version 1.0 Ready</div>
        <h1>Enterprise-Grade <span>SaaS Billing</span></h1>
        <p class="subtitle">
            A high-performance, strictly isolated, multi-tenant billing engine designed for complex recurring revenue models. Abstracting Stripe with absolute precision.
        </p>
        <div class="cta-group">
            <a href="/docs/api" class="btn btn-primary">
                Explore API Reference
            </a>
            <a href="/pulse" class="btn btn-secondary">
                View Pulse Dashboard
            </a>
        </div>
    </main>

    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3>Strict Tenant Isolation</h3>
            <p>Complete horizontal logical isolation. Every query is implicitly scoped to the active tenant via global Eloquent scopes.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3>Event-Driven Workflows</h3>
            <p>Asynchronous cross-module communication using the Transactional Outbox Pattern to guarantee event delivery under load.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3>Immutable Ledgers</h3>
            <p>Enterprise invoice engine enforcing strict immutability post-draft state alongside advanced complex Dunning flows.</p>
        </div>
    </section>
</body>
</html>
