{{-- SAVE AS: resources/views/fde/reports/ai_studio.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Report Studio — FDE Admission Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        html,
        body,
        #root {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #4bad46;
            border-radius: 4px;
        }
    </style>
</head>

<body style="background:#0a0e27;">

    <div
        style="position:fixed;top:0;left:0;right:0;z-index:9999;height:36px;background:#0d1235;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;padding:0 16px;gap:12px;">
        <a href="{{ route('fde.reports.dashboard') }}" style="font-size:11px;color:#b8bcc8;text-decoration:none;">&#8592;
            Reports</a>
        <span style="width:1px;height:14px;background:rgba(255,255,255,0.1);"></span>
        <a href="{{ route('fde.dashboard') }}" style="font-size:11px;color:#b8bcc8;text-decoration:none;">Dashboard</a>
        <span style="font-size:11px;font-weight:700;color:#fff;margin-left:auto;">&#x1F916; AI Report Studio</span>
        <span
            style="font-size:10px;color:#6b7490;">{{ optional(\App\Models\AcademicYear::where('is_active', true)->first())->name ?? '2026-27' }}</span>
    </div>

    <div id="root" style="padding-top:36px;height:100%;box-sizing:border-box;"></div>

    <script>
        window.FDE_CONTEXT = {
            academicYear: "{{ optional(\App\Models\AcademicYear::where('is_active', true)->first())->name ?? '2026-27' }}",
            csrfToken: "{{ csrf_token() }}",
            apiBase: "{{ url('/fde/api') }}",
            user: {
                name: "{{ addslashes(Auth::user()->name) }}",
                role: "{{ Auth::user()->getRoleNames()->first() ?? 'fde_cell' }}"
            }
        };
    </script>

    <script type="text/babel"
            src="{{ asset('js/ai_agent_studio.jsx') }}"
            data-presets="react">
    </script>
</body>

</html>
