@once
    <style>
        .starter-toast-stack {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            pointer-events: none;
            position: fixed;
            right: 1rem;
            top: 1rem;
            width: min(24rem, calc(100vw - 2rem));
            z-index: 2060;
        }

        .starter-toast {
            align-items: flex-start;
            background: var(--tblr-primary);
            border: 0;
            border-radius: var(--tblr-border-radius);
            box-shadow: 0 .75rem 1.75rem rgba(24, 36, 51, .22);
            color: #fff;
            display: flex;
            gap: 1rem;
            min-height: 5.25rem;
            opacity: 0;
            overflow: hidden;
            padding: 1.05rem 1rem 1rem;
            pointer-events: auto;
            position: relative;
            transform: translateY(-.5rem);
            transition: opacity .18s ease, transform .18s ease;
        }

        .starter-toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .starter-toast.is-leaving {
            opacity: 0;
            transform: translateY(-.35rem);
        }

        .starter-toast-success {
            background: var(--tblr-success);
        }

        .starter-toast-info {
            background: var(--tblr-info);
        }

        .starter-toast-warning {
            background: var(--tblr-warning);
        }

        .starter-toast-danger {
            background: var(--tblr-danger);
        }

        .starter-toast-icon {
            align-items: center;
            display: inline-flex;
            flex: 0 0 3rem;
            height: 3rem;
            justify-content: center;
            margin-top: .1rem;
            opacity: .96;
            width: 3rem;
        }

        .starter-toast-icon svg {
            filter: drop-shadow(0 .1rem .12rem rgba(0, 0, 0, .16));
            height: 3rem;
            width: 3rem;
        }

        .starter-toast-body {
            flex: 1;
            min-width: 0;
        }

        .starter-toast-title {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.35;
            margin-bottom: .2rem;
        }

        .starter-toast-message {
            color: rgba(255, 255, 255, .9);
            line-height: 1.45;
        }

        .starter-toast-close {
            --tblr-btn-close-color: #fff;
            filter: invert(1) grayscale(100%) brightness(200%);
            flex: 0 0 auto;
            margin-top: .15rem;
            opacity: .72;
        }

        .starter-toast-close:hover,
        .starter-toast-close:focus {
            opacity: 1;
        }

        .starter-toast-progress {
            animation: starter-toast-progress var(--starter-toast-duration, 4500ms) linear forwards;
            background: rgba(0, 0, 0, .22);
            bottom: 0;
            height: .35rem;
            left: 0;
            position: absolute;
            transform-origin: left;
            width: 100%;
        }

        @keyframes starter-toast-progress {
            to {
                transform: scaleX(0);
            }
        }

        @media (max-width: 575.98px) {
            .starter-toast-stack {
                left: .75rem;
                right: .75rem;
                top: .75rem;
                width: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .starter-toast {
                transition: none;
            }

            .starter-toast-progress {
                animation: none;
            }
        }
    </style>
@endonce

<div class="starter-toast-stack" data-starter-toast-stack aria-live="polite" aria-atomic="false"></div>
