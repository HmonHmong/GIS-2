<?php
// includes/header.php - อัปเดต Layout, เพิ่มเมนูตั้งค่าระบบ
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$user_full_name = $_SESSION['full_name'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'viewer';
$user_initial = mb_substr($user_full_name, 0, 1, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบ GIS สำนักช่าง อบจ.ศรีสะเกษ V2.5</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#ea580c">
    <link rel="apple-touch-icon" href="https://placehold.co/512x512/ea580c/ffffff.png?text=GIS">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Sarabun', sans-serif; -webkit-font-smoothing: antialiased; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .nav-active {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3);
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">

    <!-- Mobile Top Bar -->
    <div class="lg:hidden fixed top-0 left-0 w-full h-16 bg-white/90 backdrop-blur-md border-b border-slate-200 z-50 flex items-center justify-between px-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="bg-orange-600 p-2 rounded-xl text-white"><i data-lucide="map" size="20"></i></div>
            <div>
                <h1 class="font-black text-slate-900 text-sm leading-none">SSK GIS</h1>
                <p class="text-[8px] font-bold text-slate-400 uppercase">Si Sa Ket PAO</p>
            </div>
        </div>
        <button id="mobileMenuBtn" class="p-2 text-slate-600 bg-slate-100 rounded-xl hover:text-orange-600 active:scale-95 transition-all"><i data-lucide="menu" size="24"></i></button>
    </div>

    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity opacity-0"></div>

    <aside id="sidebar" class="w-72 bg-white h-full shadow-2xl lg:shadow-none lg:border-r border-slate-100 flex flex-col fixed lg:relative z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shrink-0">
        <div class="p-8 pb-4 shrink-0 hidden lg:block text-center border-b border-slate-50">
            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-[1.5rem] flex items-center justify-center mx-auto mb-4 text-white"><i data-lucide="map" size="32"></i></div>
            <h1 class="text-xl font-black text-slate-900 leading-none uppercase">ระบบสารสนเทศ GIS</h1>
            <p class="text-[9px] font-black text-slate-400 mt-2">องค์การบริหารส่วนจังหวัดศรีสะเกษ V2.5</p>
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar p-5 space-y-8 mt-16 lg:mt-0">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 pl-2 border-l-2 border-orange-500">เมนูหลัก (Main Menu)</p>
                <div class="space-y-1.5">
                    <a href="index.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'index.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="layout-dashboard" size="18"></i> แผงควบคุม</a>
                    <a href="projects.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= in_array($current_page, ['projects.php', 'add_project.php', 'edit_project.php', 'view_project.php']) ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="folders" size="18"></i> ทะเบียนโครงการ</a>
                    <a href="map_full.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'map_full.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="map-pinned" size="18"></i> แผนที่สารสนเทศ</a>
                    <a href="statistics.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= in_array($current_page, ['statistics.php', 'reports.php']) ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="bar-chart-2" size="18"></i> รายงานสถิติ</a>
                    <a href="export_projects.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'export_projects.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="file-spreadsheet" size="18"></i> ส่งออกข้อมูล (Excel)</a>
                </div>
            </div>

            <?php if ($user_role === 'admin'): ?>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 pl-2 border-l-2 border-slate-900">การจัดการระบบ (Admin)</p>
                <div class="space-y-1.5">
                    <a href="users.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= in_array($current_page, ['users.php', 'add_user.php', 'edit_user.php']) ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="users" size="18"></i> จัดการเจ้าหน้าที่</a>
                    <a href="activity_logs.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'activity_logs.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="history" size="18"></i> บันทึกกิจกรรม</a>
                    <a href="backup.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= in_array($current_page, ['backup.php', 'backup_system.php']) ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="database-backup" size="18"></i> สำรองข้อมูล</a>
                    <a href="system_maintenance.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'system_maintenance.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="wrench" size="18"></i> บำรุงรักษา</a>
                    <!-- ⭐️ เมนูตั้งค่าระบบอยู่ที่นี่ครับ ⭐️ -->
                    <a href="settings.php" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'settings.php' ? 'nav-active' : 'text-slate-600 hover:bg-slate-50' ?>"><i data-lucide="settings" size="18"></i> ตั้งค่าระบบ</a>
                    <a href="trash.php" class="flex items-center justify-between px-5 py-3.5 rounded-2xl font-bold text-sm transition-all <?= $current_page == 'trash.php' ? 'nav-active' : 'text-rose-500 hover:bg-rose-50' ?>">
                        <div class="flex items-center gap-4"><i data-lucide="trash-2" size="18"></i> ถังขยะโครงการ</div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50/50">
            <div class="flex items-center gap-3 mb-4 p-2">
                <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center font-black shrink-0"><?= $user_initial ?></div>
                <div class="overflow-hidden">
                    <p class="text-xs font-black text-slate-800 truncate"><?= htmlspecialchars($user_full_name) ?></p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase truncate"><?= htmlspecialchars($user_role) ?></p>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('ยืนยันการออกจากระบบ?')" class="flex items-center justify-center gap-2 w-full py-3 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-rose-50 hover:text-rose-600 shadow-sm"><i data-lucide="log-out" size="16"></i> ออกจากระบบ</a>
        </div>
    </aside>

    <main class="flex-1 h-screen overflow-y-auto bg-slate-50 relative w-full">
        <div class="min-h-full flex flex-col pt-20 lg:pt-8 px-4 lg:px-8">
            <div class="flex-1 w-full pb-10">
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const mobileBtn = document.getElementById('mobileMenuBtn');
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('sidebarOverlay');
                        let isMenuOpen = false;
                        function toggleMenu() {
                            isMenuOpen = !isMenuOpen;
                            if (isMenuOpen) {
                                sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden');
                                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                                document.body.style.overflow = 'hidden'; 
                            } else {
                                sidebar.classList.add('-translate-x-full'); overlay.classList.add('opacity-0');
                                setTimeout(() => overlay.classList.add('hidden'), 300);
                                document.body.style.overflow = '';
                            }
                        }
                        if (mobileBtn && sidebar && overlay) { mobileBtn.addEventListener('click', toggleMenu); overlay.addEventListener('click', toggleMenu); }
                    });
                </script>