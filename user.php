<?php
// users.php - ระบบจัดการเจ้าหน้าที่ (ฉบับสมบูรณ์ ป้องกัน CSRF, XSS และ SQL Injection)
require_once 'auth_check.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

// เฉพาะ Admin เท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: index.php"); 
    exit; 
}

// สร้าง CSRF Token หากยังไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = ''; 
$error = '';

// บันทึกการ เพิ่ม / แก้ไข ข้อมูลเจ้าหน้าที่ (ตรวจสอบ CSRF Token)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) === false) {
        die("CSRF Token Validation Failed.");
    }

    $action = $_POST['action'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = isset($_POST['position']) ? trim($_POST['position']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'viewer';
    $status = isset($_POST['status']) ? $_POST['status'] : 'inactive';
    
    // ป้องกัน XSS เวลานำข้อความไปแสดงผล
    $safe_full_name = htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8');
    
    if ($action === 'add') {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, position, phone, email, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $username, $password, $full_name, $position, $phone, $email, $role, $status);
        
        if ($stmt->execute()) { 
            $msg = "เพิ่มเจ้าหน้าที่ $safe_full_name สำเร็จ"; 
            log_activity($conn, 'INSERT', 'user', $conn->insert_id, "เพิ่มเจ้าหน้าที่ใหม่: $username");
        } else { 
            // ตรวจสอบ Error ว่าเกิดจากข้อมูลซ้ำ (Error Code 1062) หรือระบบ
            if ($conn->errno === 1062) {
                $error = "ชื่อผู้ใช้งานนี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น"; 
            } else {
                $error = "เกิดข้อผิดพลาดจากระบบฐานข้อมูล: " . htmlspecialchars($conn->error);
            }
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['user_id']);
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, password=?, full_name=?, position=?, phone=?, email=?, role=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $username, $password, $full_name, $position, $phone, $email, $role, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, position=?, phone=?, email=?, role=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssi", $username, $full_name, $position, $phone, $email, $role, $status, $id);
        }
        
        if ($stmt->execute()) { 
            $msg = "อัปเดตข้อมูลเจ้าหน้าที่ $safe_full_name สำเร็จ"; 
            log_activity($conn, 'UPDATE', 'user', $id, "แก้ไขข้อมูลเจ้าหน้าที่: $username");
        } else { 
            if ($conn->errno === 1062) {
                $error = "ชื่อผู้ใช้งานนี้มีคนใช้แล้ว"; 
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล"; 
            }
        }
    }
}

// บันทึกการ ลบ เจ้าหน้าที่ (ตรวจสอบ CSRF Token)
if (isset($_GET['del']) && isset($_GET['csrf'])) {
    if (hash_equals($_SESSION['csrf_token'], $_GET['csrf'])) {
        $del_id = intval($_GET['del']);
        if ($del_id !== $_SESSION['user_id']) { 
            // ปรับไปใช้ Prepared Statement แทนการต่อ String
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $msg = "ลบข้อมูลเจ้าหน้าที่สำเร็จ";
                log_activity($conn, 'DELETE', 'user', $del_id, "ลบเจ้าหน้าที่ ID: $del_id");
            } else {
                $error = "ไม่สามารถลบข้อมูลได้";
            }
        } else { 
            $error = "ไม่สามารถลบบัญชีของตัวคุณเองที่กำลังใช้งานอยู่ได้"; 
        }
    } else {
        $error = "การเรียกใช้ไม่ปลอดภัย (Invalid Token)";
    }
}

// ดึงข้อมูลเจ้าหน้าที่ทั้งหมดมาแสดง
$users = $conn->query("SELECT * FROM users ORDER BY role ASC, created_at DESC");

include 'includes/header.php';
?>

<!-- HTML UI ส่วนหัวยังคงเดิม ... ขอข้ามไปที่ส่วนปรับปรุง Link ลบ และ Form Modal -->
<div class="max-w-[1600px] mx-auto pb-20 animate-in fade-in duration-500 px-4 w-full">
    
    <!-- (ส่วนแจ้งเตือน Alert และส่วน Header คงเดิม) -->

    <div class="bg-white rounded-[3.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <!-- (ส่วน thead คงเดิม) -->
                <tbody class="divide-y divide-slate-50">
                    <?php if($users && $users->num_rows > 0): while($row = $users->fetch_assoc()): 
                        $pos = isset($row['position']) && $row['position'] !== '' ? $row['position'] : 'ไม่ระบุตำแหน่ง';
                        $phone = isset($row['phone']) && $row['phone'] !== '' ? $row['phone'] : '-';
                        $email = isset($row['email']) && $row['email'] !== '' ? $row['email'] : '-';
                    ?>
                    <!-- (ส่วนแสดงผล Column อื่นๆ คงเดิม) -->
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        
                        <!-- ปรับปรุงลิงก์ลบ เพิ่ม CSRF Token -->
                        <td class="py-5 px-8 text-right">
                            <div class="flex justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick='openModal("edit", <?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors" title="แก้ไข">
                                    <i data-lucide="edit-2" size="14"></i>
                                </button>
                                <?php if($row['id'] !== $_SESSION['user_id']): ?>
                                <a href="users.php?del=<?= $row['id'] ?>&csrf=<?= $_SESSION['csrf_token'] ?>" onclick="return confirm('ยืนยันการลบเจ้าหน้าที่คนนี้? ข้อมูลจะไม่สามารถกู้คืนได้')" class="w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-colors" title="ลบ">
                                    <i data-lucide="trash-2" size="14"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ปรับปรุง Form Modal เพิ่ม CSRF Token -->
<div id="userModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[3rem] shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]" id="userModalContent">
        <!-- (ส่วน Header Modal คงเดิม) -->
        
        <div class="p-6 md:p-8 overflow-y-auto no-scrollbar flex-1">
            <form method="POST" class="space-y-6">
                <!-- เพิ่ม CSRF Token ในฟอร์มที่ซ่อนอยู่ -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId" value="">
                
                <!-- (Input Fields อื่นๆ คงเดิม) -->
                
                <div class="sticky bottom-0 bg-white pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[2rem] shadow-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 active:scale-95 uppercase tracking-widest">
                        <i data-lucide="save" size="20"></i> บันทึกข้อมูลเจ้าหน้าที่
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- (ส่วน JavaScript คงเดิม) -->
<?php include 'includes/footer.php'; ?>