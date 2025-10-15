<?php
const TERMS_VERSION_CURRENT = '2025-10-14';
const TERMS_DIR = __DIR__ . '/agreements';
function termsFile(string $version): string {
  return TERMS_DIR . "/terms_v{$version}.html";
}
?>