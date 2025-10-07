<?php
$passwordPlano = 'Admin1234';
$hash = '$2y$10$BfhE.cLFrRghm7QFu8olBuHn9Vemw5wdedQVGYHT/nOYEmchR/ZJO';

var_dump(password_verify($passwordPlano, $hash));
$hash = '$2y$10$Mgrqs3H0mgMNog7SOj2q7OpgVOKpfU8dVgLoENA34OK1FYhOfI1Lm';

if (password_verify('Admin1234', $hash)) {
    echo '✅ Coinciden';
} else {
    echo '❌ No coinciden';
}
