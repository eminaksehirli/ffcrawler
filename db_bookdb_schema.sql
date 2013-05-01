CREATE TABLE "sources" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "source" TEXT
);
CREATE TABLE "books" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "author" TEXT,
    "title" TEXT,
    "filename" TEXT,
    "type" INTEGER,
    "link" TEXT,
    "source" TEXT,
    "state" INTEGER,
    "language_id" INTEGER
);
CREATE TABLE "states" (
    "id" INTEGER PRIMARY KEY NOT NULL,
    "name" TEXT
);

CREATE TABLE "types" (
    "id" INTEGER PRIMARY KEY NOT NULL,
    "type" TEXT,
    "humane" TEXT,
    "extension" TEXT
);

CREATE UNIQUE INDEX "books_ix_link_source" on books (link ASC, source ASC);
CREATE TABLE "languages" (
    "id" INTEGER PRIMARY KEY NOT NULL,
    "humane" TEXT NOT NULL,
    "short" TEXT NOT NULL
);

