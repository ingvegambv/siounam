<?php

echo "<h2>Hash generado</h2>";

echo password_hash(
    "ingvega",
    PASSWORD_DEFAULT
);