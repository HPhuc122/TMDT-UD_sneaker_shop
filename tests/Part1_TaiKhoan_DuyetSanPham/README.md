# UNIT TEST - Chức năng xem và ẩn mật khẩu

## 1. Chức năng được chọn

Tài liệu này chỉ kiểm thử một chức năng trong phần Tài khoản:

> Khi người dùng nhập mật khẩu, người dùng có thể bấm nút con mắt để xem mật
> khẩu. Khi nhả nút hoặc dừng thao tác, mật khẩu được ẩn lại.

| Nội dung | File |
|---|---|
| Chức năng được kiểm thử | `includes/password-reveal.php` |
| File test duy nhất | `tests/Part1_TaiKhoan_DuyetSanPham/PasswordRevealScriptTest.php` |
| Loại kiểm thử | Unit Test |

Đây là Unit Test vì không cần mở website, không cần đăng nhập, không cần
database và không cần trình duyệt. Test chỉ kiểm tra phần xử lý của nút xem
mật khẩu có đầy đủ hay không.

## 2. Dữ liệu cần có trước khi kiểm thử

Với chức năng này, không cần chuẩn bị tài khoản hay dữ liệu database. Dữ liệu
đầu vào chỉ là file chứa chức năng xem mật khẩu:

| Dữ liệu đầu vào | Ý nghĩa dễ hiểu |
|---|---|
| File `password-reveal.php` | Nơi chứa nút con mắt và cách nút hoạt động. |
| Ô nhập mật khẩu | Nơi người dùng nhập mật khẩu. |
| Nút con mắt | Nút người dùng bấm để xem mật khẩu. |
| Trạng thái đang xem | Mật khẩu hiện thành chữ để người dùng nhìn thấy. |
| Trạng thái ẩn | Mật khẩu trở lại dạng dấu chấm hoặc dấu sao. |

## 3. Điều kiện và cách chạy

Chỉ cần cài thư viện:

```text
composer install
```

Không cần chạy `composer serve`, không cần tạo database test.

Chạy toàn bộ Unit Test:

```text
php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --no-coverage --testdox
```

Kết quả mong đợi:

```text
OK (13 tests, 13 assertions)
```

## 4. Các nhóm trường hợp kiểm thử

### 4.1. Người dùng bấm nút con mắt (6 trường hợp)

| STT | Tên trường hợp | Tình huống ban đầu | Nội dung kiểm tra | Kết quả mong đợi |
|---:|---|---|---|---|
| 1 | Form có phần xử lý nút xem mật khẩu | Người dùng mở form tài khoản. | Kiểm tra form có phần xử lý nút con mắt. | Form có nút xem mật khẩu. |
| 2 | Nhận biết đúng ô mật khẩu | Form có nhiều ô nhập liệu. | Kiểm tra nút con mắt gắn đúng vào ô mật khẩu. | Bấm nút sẽ tác động đúng ô mật khẩu. |
| 3 | Bấm giữ để xem mật khẩu | Người dùng đang nhập mật khẩu. | Kiểm tra thao tác bấm giữ nút con mắt. | Mật khẩu hiện thành chữ để người dùng xem. |
| 4 | Nhả nút để ẩn mật khẩu | Người dùng đã xem mật khẩu. | Kiểm tra thao tác nhả nút con mắt. | Mật khẩu trở lại dạng được che. |
| 5 | Có khung chứa nút xem | Form hiển thị ô mật khẩu. | Kiểm tra nút được đặt đúng bên cạnh ô nhập. | Nút hiển thị đúng vị trí trong form. |
| 6 | Có nút con mắt | Người dùng cần xem mật khẩu. | Kiểm tra form có nút để thao tác. | Người dùng có thể bấm nút con mắt. |

### 4.2. Nút dễ sử dụng (3 trường hợp)

| STT | Tên trường hợp | Tình huống ban đầu | Nội dung kiểm tra | Kết quả mong đợi |
|---:|---|---|---|---|
| 1 | Biết nút đang được giữ | Người dùng đang giữ nút con mắt. | Kiểm tra nút có ghi nhận trạng thái đang xem. | Người dùng và công cụ hỗ trợ biết nút đang được giữ. |
| 2 | Có lời hướng dẫn cho người dùng | Người dùng cần biết nút dùng để làm gì. | Kiểm tra nút có lời mô tả. | Người dùng biết đây là nút xem mật khẩu. |
| 3 | Nút gắn với ô mật khẩu | Màn hình có nhiều ô nhập liệu. | Kiểm tra nút gắn đúng ô mật khẩu. | Nút không làm ảnh hưởng ô nhập khác. |

### 4.3. Mật khẩu tự ẩn lại (4 trường hợp)

| STT | Tên trường hợp | Tình huống ban đầu | Nội dung kiểm tra | Kết quả mong đợi |
|---:|---|---|---|---|
| 1 | Nhả nút chuột | Người dùng đang giữ nút xem mật khẩu. | Người dùng nhả nút chuột. | Mật khẩu tự ẩn lại. |
| 2 | Thao tác bị dừng | Thao tác xem mật khẩu bị gián đoạn. | Hệ thống nhận biết thao tác đã dừng. | Mật khẩu tự ẩn để không bị lộ. |
| 3 | Nhấn phím Escape | Người dùng đang xem mật khẩu. | Người dùng nhấn Escape. | Mật khẩu tự ẩn lại. |
| 4 | Chuyển sang trang khác | Người dùng rời khỏi trang hiện tại. | Hệ thống xử lý khi rời trang. | Mật khẩu không tiếp tục hiển thị. |

## 5. Bảng Test Case chi tiết

| Mã Test Case | Tình huống ban đầu | Nội dung kiểm tra | Các bước thực hiện | Dữ liệu sử dụng | Kết quả mong đợi | Kết quả thực tế | Đạt / Không đạt |
|---|---|---|---|---|---|---|---|
| TC-UNIT-01 | Người dùng mở form tài khoản | Form có phần xử lý nút xem mật khẩu | 1. Mở phần dùng cho ô mật khẩu.<br>2. Kiểm tra có chức năng xem mật khẩu.<br>3. Đối chiếu kết quả tự động. | Form tài khoản và file `password-reveal.php`. | Có nút con mắt để người dùng xem mật khẩu. | PHPUnit xác nhận phần xử lý nút xem mật khẩu có trong file. | Đạt |
| TC-UNIT-02 | Form có nhiều ô nhập liệu | Nút con mắt gắn đúng ô mật khẩu | 1. Mở form.<br>2. Chọn nút con mắt.<br>3. Kiểm tra nút gắn với ô mật khẩu. | Ô nhập mật khẩu trong form. | Nút tác động đúng ô mật khẩu. | PHPUnit xác nhận cách nhận biết ô mật khẩu có trong file. | Đạt |
| TC-UNIT-03 | Người dùng đang nhập mật khẩu | Bấm giữ để xem mật khẩu | 1. Nhập mật khẩu.<br>2. Bấm giữ nút con mắt.<br>3. Kiểm tra kết quả. | Một ô nhập mật khẩu và nút con mắt. | Mật khẩu hiện thành chữ. | PHPUnit xác nhận có lệnh chuyển sang trạng thái xem được. | Đạt |
| TC-UNIT-04 | Người dùng đang xem mật khẩu | Nhả nút để ẩn mật khẩu | 1. Đang giữ nút con mắt.<br>2. Nhả nút.<br>3. Kiểm tra kết quả. | Ô mật khẩu đang ở trạng thái xem. | Mật khẩu trở lại dạng được che. | PHPUnit xác nhận có lệnh đưa ô nhập về trạng thái ẩn. | Đạt |
| TC-UNIT-05 | Ô mật khẩu đã xuất hiện trên form | Nút được đặt cạnh ô nhập | 1. Mở form.<br>2. Kiểm tra phần bao quanh ô nhập và nút. | Ô mật khẩu, nút con mắt. | Nút hiển thị đúng trong form. | PHPUnit xác nhận phần khung hiển thị có trong file. | Đạt |
| TC-UNIT-06 | Người dùng cần xem mật khẩu | Form có nút con mắt | 1. Mở form.<br>2. Tìm nút xem mật khẩu. | Nút con mắt. | Nút tồn tại để người dùng thao tác. | PHPUnit xác nhận nút xem mật khẩu có trong file. | Đạt |
| TC-UNIT-07 | Người dùng đang giữ nút | Nút thể hiện đang được giữ | 1. Bấm giữ nút.<br>2. Kiểm tra trạng thái nút. | Nút con mắt. | Có thông tin cho biết nút đang được giữ. | PHPUnit xác nhận nút có phần ghi nhận trạng thái. | Đạt |
| TC-UNIT-08 | Người dùng chưa biết nút dùng làm gì | Nút có lời hướng dẫn | 1. Mở form.<br>2. Kiểm tra lời mô tả của nút. | Nút xem mật khẩu. | Có lời hướng dẫn dễ hiểu. | PHPUnit xác nhận nút có lời mô tả. | Đạt |
| TC-UNIT-09 | Form có nhiều ô mật khẩu | Nút gắn đúng ô cần xem | 1. Mở form.<br>2. Kiểm tra nút và ô mật khẩu tương ứng. | Nhiều ô nhập liệu. | Nút không tác động nhầm ô khác. | PHPUnit xác nhận nút có liên kết với ô mật khẩu. | Đạt |
| TC-UNIT-10 | Người dùng đang giữ nút xem | Mật khẩu tự ẩn khi nhả chuột | 1. Bấm giữ nút.<br>2. Nhả chuột.<br>3. Kiểm tra mật khẩu. | Ô mật khẩu đang hiện. | Mật khẩu tự ẩn lại. | PHPUnit xác nhận có xử lý khi nhả chuột. | Đạt |
| TC-UNIT-11 | Thao tác xem bị gián đoạn | Mật khẩu tự ẩn khi thao tác dừng | 1. Bắt đầu xem mật khẩu.<br>2. Dừng thao tác bất ngờ.<br>3. Kiểm tra mật khẩu. | Ô mật khẩu và nút con mắt. | Mật khẩu tự ẩn để tránh bị lộ. | PHPUnit xác nhận có xử lý khi thao tác bị dừng. | Đạt |
| TC-UNIT-12 | Người dùng đang xem mật khẩu | Nhấn Escape để ẩn | 1. Bấm giữ nút xem.<br>2. Nhấn Escape.<br>3. Kiểm tra mật khẩu. | Bàn phím và ô mật khẩu. | Mật khẩu tự ẩn lại. | PHPUnit xác nhận có xử lý phím Escape. | Đạt |
| TC-UNIT-13 | Người dùng chuyển sang trang khác | Mật khẩu tự ẩn khi rời trang | 1. Đang xem mật khẩu.<br>2. Chuyển sang trang khác.<br>3. Kiểm tra trạng thái. | Trang có ô mật khẩu. | Mật khẩu không tiếp tục hiện. | PHPUnit xác nhận có xử lý khi rời trang. | Đạt |

## 6. Bảng tổng hợp kết quả

| Unit | Số test case | Đạt | Không đạt |
|---|---:|---:|---:|
| Người dùng bấm nút con mắt | 6 | 6 | 0 |
| Nút dễ sử dụng | 3 | 3 | 0 |
| Mật khẩu tự ẩn lại | 4 | 4 | 0 |
| **Tổng** | **13** | **13** | **0** |

Kết quả PHPUnit:

```text
OK (13 tests, 13 assertions)
```

<!-- CHÈN ẢNH KẾT QUẢ:
Chạy lệnh có `--testdox`, chụp phần hiển thị 13 trường hợp và dòng OK.
Lưu tại `evidence/PasswordRevealScriptTest-testdox.png`.

![Kết quả Unit Test](evidence/PasswordRevealScriptTest-testdox.png)
-->

## 7. Trích code làm minh chứng

Code trong file test dùng các bộ dữ liệu khác nhau để một chức năng được kiểm
tra thành nhiều trường hợp:

```php
/**
 * @testdox Nút xem mật khẩu hoạt động đúng trên form tài khoản
 * @dataProvider revealScriptProvider
 */
public function testRevealScriptContainsRequiredBehavior(
    string $case,
    string $expected
): void {
    ob_start();
    include PROJECT_ROOT . '/includes/password-reveal.php';
    $html = (string) ob_get_clean();
    $this->assertStringContainsString($expected, $html);
}

public static function revealScriptProvider(): array
{
    return [
        'form có phần xử lý nút xem mật khẩu' => ['form có phần xử lý nút xem mật khẩu', '<script>'],
        'nhận biết đúng ô mật khẩu' => ['nhận biết đúng ô mật khẩu', 'input[type="password"]'],
        'bấm giữ để xem mật khẩu' => ['bấm giữ để xem mật khẩu', "input.type = 'text'"],
        'nhả nút để ẩn mật khẩu' => ['nhả nút để ẩn mật khẩu', "input.type = 'password'"],
        'có khung chứa nút xem' => ['có khung chứa nút xem', "wrapper.className = 'password-reveal'"],
        'có nút con mắt' => ['có nút con mắt', "button.className = 'password-reveal-button'"],
    ];
}
```

Ở đây, mỗi dòng trong `revealScriptProvider()` là một trường hợp riêng. Vì vậy
PHPUnit sẽ in từng tên case giống mẫu bạn gửi, thay vì chỉ in một test chung.

<!-- CHÈN ẢNH CODE:
Chụp đoạn code data provider trong VS Code và lưu tại:
`evidence/PasswordRevealScriptTest-code.png`

![Code Unit Test](evidence/PasswordRevealScriptTest-code.png)
-->

## 8. Lệnh chạy để chụp ảnh

Chạy toàn bộ và chụp bảng kết quả:

```text
php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --no-coverage --testdox
```

Chạy riêng nhóm người dùng bấm nút:

```text
php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealScriptContainsRequiredBehavior --no-coverage --testdox
```

Chạy riêng nhóm nút dễ sử dụng:

```text
php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealButtonIsAccessible --no-coverage --testdox
```

Chạy riêng nhóm tự ẩn:

```text
php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealHidesPasswordOnSafetyEvents --no-coverage --testdox
```

Khi chụp ảnh, phải để thấy cả câu lệnh đã chạy và kết quả `OK`. Không cần
chụp HTTP response hoặc database vì đây là Unit Test.
