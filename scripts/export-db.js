const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

// Đọc config từ file .env (nếu có) mà không phụ thuộc package ngoài
const envPath = path.join(__dirname, '../.env');
if (fs.existsSync(envPath)) {
    const envConfig = fs.readFileSync(envPath, 'utf8');
    envConfig.split('\\n').forEach(line => {
        const match = line.match(/^([^#\\s]+)\\s*=\\s*(.*)$/);
        if (match) {
            process.env[match[1]] = match[2].trim().replace(/(^['"]|['"]$)/g, '');
        }
    });
}

// Cấu hình Database
const DB_HOST = process.env.DB_HOST || 'localhost';
const DB_USER = process.env.DB_USER || 'root';
const DB_PASS = process.env.DB_PASS || '';
const DB_NAME = process.env.DB_NAME || 'sneaker_shop'; // Thay bằng tên DB thực tế nếu khác

// Thư mục lưu trữ
const outputDir = path.join(__dirname, '../database');

// Đảm bảo thư mục tồn tại
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
    console.log(`[INFO] Đã tạo thư mục: ${outputDir}`);
}

// Tạo tên file tự động theo timestamp: backup-YYYY-MM-DD-HHmmss.sql
const now = new Date();
const year = now.getFullYear();
const month = String(now.getMonth() + 1).padStart(2, '0');
const day = String(now.getDate()).padStart(2, '0');
const hours = String(now.getHours()).padStart(2, '0');
const minutes = String(now.getMinutes()).padStart(2, '0');
const seconds = String(now.getSeconds()).padStart(2, '0');

const fileName = `backup-${year}-${month}-${day}-${hours}${minutes}${seconds}.sql`;
const filePath = path.join(outputDir, fileName);

// Lệnh mysqldump với các tùy chọn
// --add-drop-table: Thêm DROP TABLE trước mỗi lệnh CREATE TABLE
// --complete-insert: Sử dụng INSERT INTO với đầy đủ tên các cột
// --routines: Export cả stored procedures và functions nếu có
// Lưu ý: Nếu DB_PASS trống, không truyền cờ -p
const passFlag = DB_PASS ? `-p"${DB_PASS}"` : '';

// Xây dựng command (Thử dùng mysqldump trong PATH, nếu lỗi sẽ thử đường dẫn của XAMPP)
const buildCommand = (mysqldumpPath) => {
    return `"${mysqldumpPath}" -h ${DB_HOST} -u ${DB_USER} ${passFlag} --add-drop-table --complete-insert --routines ${DB_NAME} > "${filePath}"`;
};

// Hàm chạy command
const runExport = (cmd, fallbackCmd = null) => {
    console.log(`[INFO] Đang tiến hành export database '${DB_NAME}'...`);
    
    exec(cmd, (error, stdout, stderr) => {
        if (error) {
            // Cố gắng thử fallback (đường dẫn mysqldump của XAMPP trên Windows) nếu có
            if (fallbackCmd && error.message.includes('not recognized as an internal or external command')) {
                console.log(`[WARNING] Không tìm thấy mysqldump trong PATH. Thử đường dẫn XAMPP mặc định...`);
                runExport(fallbackCmd);
                return;
            }
            
            console.error(`[ERROR] Export thất bại: ${error.message}`);
            
            // Xóa file rác nếu lệnh bị lỗi
            if (fs.existsSync(filePath)) {
                fs.unlinkSync(filePath);
            }
            return;
        }

        // Kiểm tra file có được tạo ra không và có dung lượng > 0 bytes (mysqldump thành công)
        if (fs.existsSync(filePath) && fs.statSync(filePath).size > 0) {
            console.log(`[SUCCESS] Export database thành công!`);
            console.log(`[INFO] File đã được lưu tại: ${filePath}`);
        } else {
            console.error(`[ERROR] Export thất bại: Database '${DB_NAME}' có thể không tồn tại hoặc sai thông tin đăng nhập.`);
            if (fs.existsSync(filePath)) {
                fs.unlinkSync(filePath);
            }
        }
    });
};

// Chạy script
const primaryCmd = buildCommand('mysqldump');
const fallbackCmd = buildCommand('C:\\xampp\\mysql\\bin\\mysqldump.exe');

runExport(primaryCmd, fallbackCmd);
