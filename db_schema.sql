CREATE TABLE "users" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "ff_id" TEXT NOT NULL,
    "name" TEXT,
    "private" INTEGER
);
CREATE TABLE "likes" (
    "entry_id" INTEGER NOT NULL,
    "user_id" INTEGER NOT NULL,
    "date" TEXT
);
CREATE UNIQUE INDEX "likes_ix_entry_user" on likes (entry_id ASC, user_id ASC);
CREATE TABLE "thumbnails" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "entry_id" INTEGER,
    "url" TEXT,
    "link" TEXT
);
CREATE TABLE "files" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "url" TEXT,
    "name" TEXT,
    "entry_id" INTEGER,
    "type" TEXT,
    "size" INTEGER
);
CREATE TABLE entries (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "ff_id" TEXT NOT NULL,
    "body" TEXT,
    "rawBody" TEXT,
    "rawLink" TEXT,
    "date" TEXT,
    "via" TEXT,
    "user_id" INTEGER,
    "source_id" INTEGER
);
CREATE UNIQUE INDEX "entries_ix_ff_id" on entries (ff_id ASC);
CREATE UNIQUE INDEX "users_ix_ff_id" on users (ff_id ASC);
CREATE UNIQUE INDEX "thumbnails_ix_url" on thumbnails (url ASC);
CREATE UNIQUE INDEX "files_ix_url" on files (url ASC);
CREATE TABLE comments (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "ff_id" TEXT NOT NULL,
    "body" TEXT,
    "rawBody" TEXT,
    "entry_id" INTEGER,
    "user_id" INTEGER,
    "date" TEXT,
    "via" TEXT
);
CREATE UNIQUE INDEX "comments_ix_ff_id_entry_id" on comments (ff_id ASC, entry_id ASC);
CREATE TABLE sources (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "address" TEXT NOT NULL,
    "type" TEXT NOT NULL
);
CREATE TABLE "worker_data" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "worker_name" TEXT NOT NULL,
    "work_title" TEXT NOT NULL,
    "value" TEXT,
    "time" TEXT
);
