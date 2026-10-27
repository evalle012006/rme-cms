#!/usr/bin/env python3
"""
Export all MySQL base tables from a source database to UTF-8 CSV files.

Usage:
    cd scripts/export-db-to-csv
    pip install -r requirements.txt
    cp .env.example .env   # then fill in SOURCE_DB_* values
    python export_db.py

    # Or pass connection details on the CLI:
    python export_db.py --host 127.0.0.1 --port 3306 --database mydb --user root --password secret

Output:
    <repo-root>/backup-database/sheets/<table_name>.csv
"""

from __future__ import annotations

import argparse
import csv
import sys
from datetime import date, datetime, time, timedelta
from decimal import Decimal
from pathlib import Path
from typing import Any, Optional

try:
    import pymysql
    from pymysql.cursors import SSCursor
except ImportError:
    print("Missing dependency: pymysql. Run: pip install -r requirements.txt", file=sys.stderr)
    sys.exit(1)

try:
    from dotenv import load_dotenv
except ImportError:
    print("Missing dependency: python-dotenv. Run: pip install -r requirements.txt", file=sys.stderr)
    sys.exit(1)

import os

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parents[1]
DEFAULT_OUTPUT_DIR = REPO_ROOT / "backup-database" / "sheets"


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Export all MySQL base tables to CSV files under backup-database/sheets/."
    )
    parser.add_argument("--host", default=None, help="MySQL host (or SOURCE_DB_HOST)")
    parser.add_argument("--port", type=int, default=None, help="MySQL port (or SOURCE_DB_PORT)")
    parser.add_argument("--database", default=None, help="Database name (or SOURCE_DB_DATABASE)")
    parser.add_argument("--user", default=None, help="MySQL username (or SOURCE_DB_USERNAME)")
    parser.add_argument("--password", default=None, help="MySQL password (or SOURCE_DB_PASSWORD)")
    parser.add_argument(
        "--output-dir",
        default=None,
        help=f"Output directory (default: {DEFAULT_OUTPUT_DIR})",
    )
    parser.add_argument(
        "--exclude",
        default="",
        help="Comma-separated table names to skip",
    )
    parser.add_argument(
        "--overwrite",
        action="store_true",
        default=True,
        help="Overwrite existing CSV files (default: true)",
    )
    parser.add_argument(
        "--no-overwrite",
        action="store_true",
        help="Skip tables that already have a CSV file",
    )
    parser.add_argument(
        "--chunk-size",
        type=int,
        default=1000,
        help="Rows to fetch per chunk (default: 1000)",
    )
    return parser.parse_args()


def resolve_config(args: argparse.Namespace) -> dict[str, Any]:
    load_dotenv(SCRIPT_DIR / ".env")

    host = args.host or os.getenv("SOURCE_DB_HOST", "127.0.0.1")
    port = args.port if args.port is not None else int(os.getenv("SOURCE_DB_PORT", "3306"))
    database = args.database or os.getenv("SOURCE_DB_DATABASE")
    user = args.user or os.getenv("SOURCE_DB_USERNAME")
    password = args.password if args.password is not None else os.getenv("SOURCE_DB_PASSWORD", "")
    output_dir = Path(
        args.output_dir or os.getenv("OUTPUT_DIR") or DEFAULT_OUTPUT_DIR
    ).resolve()
    overwrite = not args.no_overwrite
    exclude = {name.strip() for name in args.exclude.split(",") if name.strip()}

    missing = [name for name, value in (("database", database), ("user", user)) if not value]
    if missing:
        raise SystemExit(
            f"Missing required config: {', '.join(missing)}. "
            "Pass CLI flags or set them in .env (see .env.example)."
        )

    return {
        "host": host,
        "port": port,
        "database": database,
        "user": user,
        "password": password or "",
        "output_dir": output_dir,
        "overwrite": overwrite,
        "exclude": exclude,
        "chunk_size": max(1, args.chunk_size),
    }


def connect(config: dict[str, Any]):
    return pymysql.connect(
        host=config["host"],
        port=config["port"],
        user=config["user"],
        password=config["password"],
        database=config["database"],
        charset="utf8mb4",
        cursorclass=SSCursor,
        autocommit=True,
    )


def list_base_tables(conn, database: str) -> list[str]:
    with conn.cursor() as cursor:
        cursor.execute(
            """
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = %s
              AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
            """,
            (database,),
        )
        return [row[0] for row in cursor.fetchall()]


def serialize_value(value: Any) -> Any:
    if value is None:
        return ""
    if isinstance(value, bool):
        return int(value)
    if isinstance(value, (datetime, date, time)):
        return value.isoformat()
    if isinstance(value, timedelta):
        return str(value)
    if isinstance(value, Decimal):
        return format(value, "f")
    if isinstance(value, (bytes, bytearray, memoryview)):
        return bytes(value).hex()
    return value


def export_table(
    conn,
    table: str,
    output_path: Path,
    chunk_size: int,
) -> int:
    quoted_table = f"`{table.replace('`', '``')}`"
    row_count = 0

    with conn.cursor() as cursor:
        cursor.execute(f"SELECT * FROM {quoted_table}")
        columns = [desc[0] for desc in cursor.description] if cursor.description else []

        with output_path.open("w", newline="", encoding="utf-8") as csv_file:
            writer = csv.writer(csv_file, quoting=csv.QUOTE_MINIMAL)
            writer.writerow(columns)

            while True:
                rows = cursor.fetchmany(chunk_size)
                if not rows:
                    break
                for row in rows:
                    writer.writerow([serialize_value(value) for value in row])
                    row_count += 1

    return row_count


def export_all(config: dict[str, Any]) -> int:
    output_dir: Path = config["output_dir"]
    output_dir.mkdir(parents=True, exist_ok=True)

    print(f"Connecting to {config['host']}:{config['port']}/{config['database']} ...")
    conn = connect(config)

    summary: list[tuple[str, str, Optional[int], Optional[str]]] = []
    failures = 0

    try:
        tables = list_base_tables(conn, config["database"])
        if not tables:
            print("No base tables found.")
            return 0

        print(f"Found {len(tables)} base table(s). Writing to {output_dir}")

        for table in tables:
            if table in config["exclude"]:
                print(f"  skip  {table} (excluded)")
                summary.append((table, "skipped", None, "excluded"))
                continue

            output_path = output_dir / f"{table}.csv"
            if output_path.exists() and not config["overwrite"]:
                print(f"  skip  {table} (exists, --no-overwrite)")
                summary.append((table, "skipped", None, "exists"))
                continue

            try:
                # SSCursor requires finishing/closing one query before starting another.
                # Reconnect per table so a failed stream cannot poison the connection.
                with connect(config) as table_conn:
                    rows = export_table(table_conn, table, output_path, config["chunk_size"])
                print(f"  ok    {table}: {rows} row(s) -> {output_path.name}")
                summary.append((table, "ok", rows, str(output_path)))
            except Exception as exc:  # noqa: BLE001 - continue exporting other tables
                failures += 1
                print(f"  FAIL  {table}: {exc}", file=sys.stderr)
                summary.append((table, "failed", None, str(exc)))
    finally:
        conn.close()

    print("\nSummary")
    print("-" * 60)
    for table, status, rows, detail in summary:
        if status == "ok":
            print(f"{table}: {rows} rows -> {detail}")
        else:
            print(f"{table}: {status}" + (f" ({detail})" if detail else ""))

    if failures:
        print(f"\nCompleted with {failures} failure(s).", file=sys.stderr)
        return 1

    print("\nExport completed successfully.")
    return 0


def main() -> int:
    args = parse_args()
    config = resolve_config(args)
    return export_all(config)


if __name__ == "__main__":
    sys.exit(main())
