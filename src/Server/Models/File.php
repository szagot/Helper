<?php

namespace Szagot\Helper\Server\Models;

class File
{
    const FILE_NAME          = 'name';
    const FILE_TYPE          = 'type';
    const FILE_TMP_PATH_NAME = 'tmp_name';
    const FILE_ERROR         = 'error';
    const FILE_SIZE          = 'size';

    const FILE_SIZE_BYTE = 1;
    const FILE_SIZE_KB   = 1024;
    const FILE_SIZE_MB   = 1024 * 1024;
    const FILE_SIZE_GB   = 1024 * 1024 * 1024;

    public function __construct(
        private mixed $name,
        private array $file
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getFieldName(): mixed
    {
        return $this->name;
    }

    public function getFile(): array
    {
        return $this->file;
    }

    public function getFileName(): ?string
    {
        return $this->file[self::FILE_NAME] ?? null;
    }

    public function getFileTmpPath(): ?string
    {
        return $this->file[self::FILE_TMP_PATH_NAME] ?? null;
    }

    public function getFileType(): ?string
    {
        return $this->file[self::FILE_TYPE] ?? null;
    }

    public function getFileSize(int $un = self::FILE_SIZE_BYTE): ?string
    {
        if ($un < 1) {
            $un = 1;
        }

        return ($this->file[self::FILE_SIZE] ?? 0) / $un;
    }

    public function getFileError(): ?string
    {
        return $this->file[self::FILE_ERROR] ?? null;
    }
}