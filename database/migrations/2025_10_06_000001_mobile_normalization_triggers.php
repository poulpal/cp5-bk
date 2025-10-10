<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL 8 required for REGEXP_REPLACE
        // 1) Function to convert Persian/Arabic digits to English
        DB::unprepared(<<<'SQL'
CREATE FUNCTION fn_to_english_digits(s VARCHAR(64)) RETURNS VARCHAR(64)
DETERMINISTIC
BEGIN
    IF s IS NULL THEN RETURN NULL; END IF;
    -- Persian digits
    SET s = REPLACE(s, '۰','0');
    SET s = REPLACE(s, '۱','1');
    SET s = REPLACE(s, '۲','2');
    SET s = REPLACE(s, '۳','3');
    SET s = REPLACE(s, '۴','4');
    SET s = REPLACE(s, '۵','5');
    SET s = REPLACE(s, '۶','6');
    SET s = REPLACE(s, '۷','7');
    SET s = REPLACE(s, '۸','8');
    SET s = REPLACE(s, '۹','9');
    -- Arabic digits
    SET s = REPLACE(s, '٠','0');
    SET s = REPLACE(s, '١','1');
    SET s = REPLACE(s, '٢','2');
    SET s = REPLACE(s, '٣','3');
    SET s = REPLACE(s, '٤','4');
    SET s = REPLACE(s, '٥','5');
    SET s = REPLACE(s, '٦','6');
    SET s = REPLACE(s, '٧','7');
    SET s = REPLACE(s, '٨','8');
    SET s = REPLACE(s, '٩','9');
    RETURN s;
END
SQL);

        // 2) Core normalization function → returns 11-digit "09XXXXXXXXX" when possible
        DB::unprepared(<<<'SQL'
CREATE FUNCTION fn_normalize_iran_mobile(s VARCHAR(64)) RETURNS VARCHAR(16)
DETERMINISTIC
BEGIN
    DECLARE d VARCHAR(64);
    IF s IS NULL THEN RETURN NULL; END IF;

    SET d = fn_to_english_digits(s);
    SET d = REGEXP_REPLACE(d, '[^0-9]+', '');

    IF LEFT(d,4)='0098' THEN SET d = SUBSTRING(d,5);
    ELSEIF LEFT(d,3)='098' THEN SET d = SUBSTRING(d,4);
    ELSEIF LEFT(d,2)='98' THEN SET d = SUBSTRING(d,3);
    END IF;

    IF LENGTH(d)=10 AND LEFT(d,1)='9' THEN SET d = CONCAT('0', d); END IF;

    IF LENGTH(d)=11 AND LEFT(d,2)='09' THEN
        RETURN d;
    END IF;

    -- try to salvage common pattern (any 9xxxxxxxxx within the string)
    IF d REGEXP '9[0-9]{9}' THEN
        RETURN CONCAT('0', SUBSTRING(REGEXP_SUBSTR(d, '9[0-9]{9}'), 1, 10));
    END IF;

    RETURN d; -- fallback: cleaned digits (will keep odd cases visible to admin)
END
SQL);

        // 3) Triggers for users, operators, contacts
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_users_mobile_bi;
CREATE TRIGGER trg_users_mobile_bi BEFORE INSERT ON users FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_users_mobile_bu;
CREATE TRIGGER trg_users_mobile_bu BEFORE UPDATE ON users FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_operators_mobile_bi;
CREATE TRIGGER trg_operators_mobile_bi BEFORE INSERT ON operators FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_operators_mobile_bu;
CREATE TRIGGER trg_operators_mobile_bu BEFORE UPDATE ON operators FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_contacts_mobile_bi;
CREATE TRIGGER trg_contacts_mobile_bi BEFORE INSERT ON contacts FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_contacts_mobile_bu;
CREATE TRIGGER trg_contacts_mobile_bu BEFORE UPDATE ON contacts FOR EACH ROW
BEGIN
    SET NEW.mobile = fn_normalize_iran_mobile(NEW.mobile);
END;
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_users_mobile_bi;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_users_mobile_bu;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_operators_mobile_bi;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_operators_mobile_bu;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_contacts_mobile_bi;');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_contacts_mobile_bu;');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_normalize_iran_mobile;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_to_english_digits;');
    }
};
