.DEFAULT_GOAL := check

.PHONY: check
check: pycodestyle flake8 mypy

.PHONY: pycodestyle
pycodestyle:
	@echo $@
	@pycodestyle . --exclude=vendor --ignore=E501,W503 # E501 = long lines; W503 = indent for [] + []
	@echo "[OK]"

.PHONY: flake8
flake8:
	@echo $@
	@flake8 . --exclude=vendor --ignore=E501
	@echo "[OK]"

.PHONY: mypy
mypy:
	@echo $@
	@mypy scraper/*.py --cache-dir=${0%/*}temp/mypy_cache --namespace-packages --warn-unused-configs --show-error-codes
	@echo "[OK]"
