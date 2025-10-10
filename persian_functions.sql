USE cp5;

DELIMITER $$

DROP FUNCTION IF EXISTS PDAY;
$$

CREATE FUNCTION PDAY(dt DATETIME)
RETURNS INT
DETERMINISTIC
NO SQL
BEGIN
    DECLARE g_y, g_m, g_d INT;
    DECLARE jd, temp INT;
    DECLARE j_y, j_m, j_d INT;
    
    SET g_y = YEAR(dt);
    SET g_m = MONTH(dt);
    SET g_d = DAY(dt);
    
    SET jd = 1461 * (g_y + 4800 + (g_m - 14) DIV 12) DIV 4 +
             367 * (g_m - 2 - 12 * ((g_m - 14) DIV 12)) DIV 12 -
             3 * ((g_y + 4900 + (g_m - 14) DIV 12) DIV 100) DIV 4 +
             g_d - 32075;
    
    SET temp = jd - 2121446;
    SET j_y = (4 * temp + 139) DIV 146097;
    SET temp = temp - (146097 * j_y + 3) DIV 4;
    SET j_d = (5000 * temp + 2) DIV 153001;
    SET j_m = (j_d + 3) DIV 11;
    SET j_d = temp - (153001 * j_d - 2) DIV 5000 + 1;
    SET j_y = j_y - 1595 + j_m;
    SET j_m = j_d + 1 - 12 * j_m;
    
    RETURN j_d;
END;
$$

DROP FUNCTION IF EXISTS PMONTH;
$$

CREATE FUNCTION PMONTH(dt DATETIME)
RETURNS INT
DETERMINISTIC
NO SQL
BEGIN
    DECLARE g_y, g_m, g_d INT;
    DECLARE jd, temp INT;
    DECLARE j_y, j_m, j_d INT;
    
    SET g_y = YEAR(dt);
    SET g_m = MONTH(dt);
    SET g_d = DAY(dt);
    
    SET jd = 1461 * (g_y + 4800 + (g_m - 14) DIV 12) DIV 4 +
             367 * (g_m - 2 - 12 * ((g_m - 14) DIV 12)) DIV 12 -
             3 * ((g_y + 4900 + (g_m - 14) DIV 12) DIV 100) DIV 4 +
             g_d - 32075;
    
    SET temp = jd - 2121446;
    SET j_y = (4 * temp + 139) DIV 146097;
    SET temp = temp - (146097 * j_y + 3) DIV 4;
    SET j_d = (5000 * temp + 2) DIV 153001;
    SET j_m = (j_d + 3) DIV 11;
    SET j_d = temp - (153001 * j_d - 2) DIV 5000 + 1;
    SET j_y = j_y - 1595 + j_m;
    SET j_m = j_d + 1 - 12 * j_m;
    
    RETURN j_m;
END;
$$

DROP FUNCTION IF EXISTS PYEAR;
$$

CREATE FUNCTION PYEAR(dt DATETIME)
RETURNS INT
DETERMINISTIC
NO SQL
BEGIN
    DECLARE g_y, g_m, g_d INT;
    DECLARE jd, temp INT;
    DECLARE j_y, j_m, j_d INT;
    
    SET g_y = YEAR(dt);
    SET g_m = MONTH(dt);
    SET g_d = DAY(dt);
    
    SET jd = 1461 * (g_y + 4800 + (g_m - 14) DIV 12) DIV 4 +
             367 * (g_m - 2 - 12 * ((g_m - 14) DIV 12)) DIV 12 -
             3 * ((g_y + 4900 + (g_m - 14) DIV 12) DIV 100) DIV 4 +
             g_d - 32075;
    
    SET temp = jd - 2121446;
    SET j_y = (4 * temp + 139) DIV 146097;
    SET temp = temp - (146097 * j_y + 3) DIV 4;
    SET j_d = (5000 * temp + 2) DIV 153001;
    SET j_m = (j_d + 3) DIV 11;
    SET j_y = j_y - 1595 + j_m;
    
    RETURN j_y;
END;
$$

DELIMITER ;
