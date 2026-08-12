<style>
    .visor-panel {
        flex: 1 1 50%;
        min-width: 0;
        border-radius: .25rem;
        overflow: auto;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .visor-panel--dark {
        background: #12151c;
        border: 1px solid #232733;
    }

    .visor-panel--light {
        background: #fff;
        border: 1px solid #dee2e6;
    }

    .visor-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        color: #6c757d;
        padding: 2rem;
        text-align: center;
    }

    .visor-empty-icon {
        position: relative;
        width: 96px;
        height: 96px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #007bff;
        font-size: 2rem;
    }

    .visor-empty-icon::before,
    .visor-empty-icon::after {
        content: "";
        position: absolute;
        inset: 0;
        border: 2px dashed #ced4da;
        border-radius: .9rem;
    }

    .visor-empty-icon::before {
        transform: rotate(-8deg);
    }

    .visor-empty-icon::after {
        transform: rotate(8deg);
    }

    .visor-error .visor-empty-icon,
    .visor-error .visor-empty-icon::before,
    .visor-error .visor-empty-icon::after {
        color: #dc3545;
        border-color: #f1aeb5;
    }

    .visor-panel-ficha {
        padding: 1.5rem;
        color: #212529;
    }

    .visor-ficha-header {
        margin: -1.5rem -1.5rem 1.5rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #4e73df, #6f42c1);
        color: #fff;
    }

    .visor-ficha-header h2 {
        margin: 0 0 .25rem;
        font-size: 1.2rem;
        color: #fff;
    }

    .visor-ficha-header .visor-muted {
        color: rgba(255, 255, 255, .85);
    }

    .visor-muted {
        color: #6c757d;
        font-size: .85rem;
    }

    .visor-block {
        margin-bottom: 1.25rem;
        padding: 1rem 1.25rem;
        border-radius: .5rem;
        border-left: 4px solid var(--accent, #6c757d);
        background: var(--accent-bg, #f8f9fa);
    }

    .visor-block h4 {
        color: var(--accent, #007bff);
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .6rem;
    }

    .visor-block--paciente { --accent: #4e73df; --accent-bg: #eef2fd; }
    .visor-block--medico { --accent: #17a2b8; --accent-bg: #e8f7f9; }
    .visor-block--institucion { --accent: #6f42c1; --accent-bg: #f3edfb; }
    .visor-block--problemas { --accent: #fd7e14; --accent-bg: #fff4e8; }
    .visor-block--medicacion { --accent: #198754; --accent-bg: #e9f7ef; }
    .visor-block--alergias { --accent: #dc3545; --accent-bg: #fceaec; }
    .visor-block--procedimientos { --accent: #0d6efd; --accent-bg: #e9f1ff; }
    .visor-block--encuentros { --accent: #20c997; --accent-bg: #e6faf5; }
    .visor-block--resultados { --accent: #6610f2; --accent-bg: #efe8fd; }
    .visor-block--inmunizaciones { --accent: #e64980; --accent-bg: #fdeef4; }
    .visor-block--otro { --accent: #6c757d; --accent-bg: #f1f2f3; }

    .visor-block dl {
        display: grid;
        grid-template-columns: 130px 1fr;
        row-gap: .4rem;
        margin: 0;
    }

    .visor-block dt {
        color: #6c757d;
        font-weight: 400;
    }

    .visor-block dd {
        margin: 0;
    }

    .visor-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .visor-badge {
        display: inline-block;
        background: #fff;
        color: var(--accent, #4e73df);
        border: 1px solid var(--accent, #4e73df);
        border-radius: .25rem;
        padding: .15rem .45rem;
        font-size: .75rem;
        margin: 0 .35rem .35rem 0;
    }

    .visor-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .visor-list li {
        padding: .6rem .25rem .6rem .95rem;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        position: relative;
    }

    .visor-list li::before {
        content: "";
        position: absolute;
        left: 0;
        top: 1.1rem;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent, #6c757d);
    }

    .visor-list li:last-child {
        border-bottom: 0;
    }

    .visor-list li strong {
        display: block;
    }

    .visor-tag {
        display: inline-block;
        font-size: .7rem;
        font-weight: 600;
        padding: .1rem .5rem;
        border-radius: 1rem;
        margin-left: .5rem;
        vertical-align: middle;
    }

    .visor-tag--success { background: #d1f2e0; color: #0f6f47; }
    .visor-tag--warning { background: #ffe8cc; color: #a0530a; }
    .visor-tag--danger { background: #fbdadf; color: #a3212f; }
    .visor-tag--info { background: #d3ecf3; color: #0a6d84; }
    .visor-tag--secondary { background: #e2e3e5; color: #4a4f54; }
</style>
