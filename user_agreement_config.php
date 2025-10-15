<?php
const TERMS_VERSION_CURRENT = '2025-10-16';
const TERMS_DIR = __DIR__ . '/user-agreements';

function termsFile(string $version): string {
  return TERMS_DIR . "/agreement_v{$version}.html";
}
?>