<?php
$passwordPlano = 'Admin1234';
$hash = '$2y$10$BfhE.cLFrRghm7QFu8olBuHn9Vemw5wdedQVGYHT/nOYEmchR/ZJO';

var_dump(password_verify($passwordPlano, $hash));
