# 2021-03-13 HSteeb
# makefile for usfm-tools


# === RULES ==========================

# have non-dangerous first target...
.PHONY: info
info:
	@echo ""
	@echo "All Tests"
	@echo ""
	@echo "  - test       : runs unit tests and integration tests"
	@echo ""
	@echo "Unit Tests"
	@echo "  - phpunit    : runs PHP unit tests"
	@echo "To run an individual test case:"
	@echo "  - vendor/bin/phpunit --filter testcase"
	@echo ""
	@echo "Build"
	@echo "  - build      : creates standalone .php files"
	@echo ""
	@echo "Integration test"
	@echo "  - itest      : create files"
	@echo "  - idiff      : compare results"
	@echo "  - isave      : record results"
	@echo ""
	@echo "General"
	@echo "  - info       : this text"



# === All Tests ====================

.PHONY: test 
test: itest phpunit

# === Unit Test ====================
.PHONY: phpunit
phpunit:
	@vendor/bin/phpunit

# === Build ===================
build:
	@cat src/sanitizeSrc.php src/Sanitizer.php src/Replacer.php | grep -v namespace | grep -v 'use HSteeb' | grep -v 'vendor/autoload.php' > sanitize.php

# === itest ===================
ITEST=test/itest
.PHONY: itest idiff isave
itest: build
	mkdir -p /tmp/sanitizetest && php sanitize.php itest/src/Gn.usfm /tmp/sanitizetest/Gn.usfm sample/config.json && diff -r itest/ref /tmp/sanitizetest
idiff:
	diff -r itest/ref /tmp/sanitizetest
isave:
	cp -a /tmp/sanitizetest/* itest/ref/

# === Sample ===================
sample: build
	@php src/sanitizeSrc.php sample/Gn.usfm /tmp/Gn.usfm sample/config.json && diff sample/Gn.usfm /tmp/Gn.usfm

# === /RULES =========================
