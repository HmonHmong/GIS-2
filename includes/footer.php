<?php
// includes/footer.php - แก้ไข Layout แถบขาวด้านขวา และเพิ่ม PWA Service Worker
?>
            </div> <!-- ปิด wrap เนื้อหาหลักที่เปิดไว้ใน header.php -->

            <!-- ส่วนท้ายหน้าจอ (Bottom Footer) จะถูกดันลงมาล่างสุดเสมอ -->
            <footer class="mt-auto py-6 border-t border-slate-200 text-slate-400 shrink-0 w-full block">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-300 shadow-sm shrink-0">
                            <i data-lucide="shield-check" size="14"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">ระบบจัดเก็บข้อมูลพิกัดโครงสร้างพื้นฐาน</p>
                            <p class="text-[9px] font-bold mt-0.5 text-slate-400">องค์การบริหารส่วนจังหวัดศรีสะเกษ &copy; <?= date('Y') ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 text-[9px] font-black uppercase tracking-widest">
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                            <span class="text-emerald-600">Server Online</span>
                        </div>
                        <span class="text-slate-300">V. 2.5.0</span>
                    </div>
                </div>
            </footer>
        </div> <!-- ปิด flex-col ใหญ่ -->
    </main> <!-- ปิดแท็ก <main> -->

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. เรนเดอร์ไอคอน Lucide
            if(typeof lucide !== 'undefined') { lucide.createIcons(); }

            // 2. ระบบซ่อนข้อความแจ้งเตือนอัตโนมัติ (Flash Messages)
            const alerts = document.querySelectorAll('.animate-in');
            alerts.forEach(alert => {
                if(alert.classList.contains('bg-emerald-50') || alert.classList.contains('bg-rose-50') || alert.classList.contains('bg-red-50') || alert.classList.contains('bg-slate-800')) {
                    setTimeout(() => {
                        alert.style.transition = 'all 0.5s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 500);
                    }, 4000);
                }
            });
        });

        // 3. PWA: ลงทะเบียน Service Worker เพื่อทำแอปพลิเคชันมือถือ
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('✅ ServiceWorker ทำงานเรียบร้อยแล้ว: ', registration.scope);
                })
                .catch(err => {
                    console.log('❌ การลงทะเบียน ServiceWorker ล้มเหลว: ', err);
                });
            });
        }
    </script>
</body>
</html>