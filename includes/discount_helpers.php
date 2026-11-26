<?php

/** Ngày ISO từ form, CSDL hoặc ngày có múi giờ rõ ràng. */
function discountDateTimestamp($value) {
    if ($value === null || $value === '') return null;
    if (!is_string($value) || str_contains($value, "\0")) return null;
    $formats = ['!Y-m-d H:i:s', '!Y-m-d\TH:i', '!Y-m-d\TH:i:s', '!Y-m-d',
        '!Y-m-d H:i:s P', '!Y-m-d H:i:s T'];
    foreach ($formats as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format(substr($format, 1)) === $value) {
            return $date->getTimestamp();
        }
    }
    return null;
}

/** Trả lỗi theo tên trường, phân biệt ngày bỏ trống với ngày nhập sai. */
function validateDiscountDates(array $discount): array {
    $errors = [];
    foreach (['start_date' => 'Ngày bắt đầu', 'end_date' => 'Ngày kết thúc'] as $field => $label) {
        $value = $discount[$field] ?? null;
        if ($value !== null && $value !== '' && discountDateTimestamp($value) === null) {
            $errors[$field] = $label . ' không hợp lệ. Vui lòng nhập ngày và giờ tồn tại.';
        }
    }
    if ($errors) return $errors;
    $start = discountDateTimestamp($discount['start_date'] ?? null);
    $end = discountDateTimestamp($discount['end_date'] ?? null);
    if ($start !== null && $end !== null && $start > $end) {
        $errors['end_date'] = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.';
    }
    return $errors;
}

/** Dùng chung tại form quản trị và lúc áp dụng mã lấy từ CSDL. */
function validateDiscountData(array $discount): array {
    $errors = validateDiscountDates($discount);
    $type = $discount['discount_type'] ?? null;
    if (!in_array($type, ['fixed', 'percentage'], true)) {
        $errors['discount_type'] = 'Loại giảm phải là tiền cố định hoặc phần trăm.';
    }
    $value = $discount['discount_value'] ?? null;
    if (!is_numeric($value) || !is_finite((float)$value) || (float)$value <= 0) {
        $errors['discount_value'] = 'Giá trị giảm phải là số lớn hơn 0.';
    } elseif ($type === 'percentage' && (float)$value > 100) {
        $errors['discount_value'] = 'Phần trăm giảm phải lớn hơn 0 và không được vượt quá 100%.';
    }
    $cap = $discount['max_discount_amount'] ?? null;
    if ($cap !== null && (!is_numeric($cap) || !is_finite((float)$cap) || (float)$cap <= 0)) {
        $errors['max_discount_amount'] = 'Số tiền giảm tối đa phải lớn hơn 0 hoặc để trống để không giới hạn.';
    }
    return $errors;
}

function isDiscountExpired($discount, $now = null) {
    $end = discountDateTimestamp($discount['end_date'] ?? null);
    return $end !== null && ($now ?? time()) > $end;
}

function isDiscountCurrentlyValid($discount, $now = null) {
    if (validateDiscountDates($discount)) return false;
    if (array_key_exists('discount_type', $discount) && validateDiscountData($discount)) return false;
    $now = $now ?? time();
    $start = discountDateTimestamp($discount['start_date'] ?? null);
    $end = discountDateTimestamp($discount['end_date'] ?? null);

    if ($start !== null && $now < $start) return false;
    if ($end !== null && $now > $end) return false;
    if (
        isset($discount['max_uses'], $discount['total_used']) &&
        $discount['max_uses'] !== null &&
        (int)$discount['total_used'] >= (int)$discount['max_uses']
    ) {
        return false;
    }

    return true;
}

/** Tính tiền giảm sau khi mã đã qua các bước kiểm tra điều kiện áp dụng. */
function calculateDiscountAmount(array $discount, $cartTotal) {
    $errors = validateDiscountData($discount);
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }
    if (!is_numeric($cartTotal) || !is_finite((float)$cartTotal) || (float)$cartTotal < 0) {
        throw new InvalidArgumentException('Tổng giỏ hàng phải là số lớn hơn hoặc bằng 0.');
    }
    $amount = 0;
    if ($discount['discount_type'] === 'fixed') {
        $amount = $discount['discount_value'];
    } else {
        $amount = $cartTotal * ($discount['discount_value'] / 100);
        // Cap by max_discount_amount
        if (($discount['max_discount_amount'] ?? null) !== null && $amount > $discount['max_discount_amount']) {
            $amount = $discount['max_discount_amount'];
        }
    }

    // Cap discount at total amount
    if ($amount > $cartTotal) {
        $amount = $cartTotal;
    }

    return $amount;
}
