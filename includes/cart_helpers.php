<?php

/** Tính tổng giá bán nhân số lượng của các dòng trong giỏ hàng. */
function calculateCartTotal(array $cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}
