<?php
// تشفير كلمة المرور لـ admin
$new_password_admin = password_hash("12345", PASSWORD_DEFAULT);
echo "كلمة المرور المشفرة لـ admin: " . $new_password_admin . "<br>";

// تشفير كلمة المرور لـ newessam (إذا لزم الأمر)
$new_password_newessam = password_hash("123", PASSWORD_DEFAULT);
echo "كلمة المرور المشفرة لـ newessam: " . $new_password_newessam . "<br>";
?>