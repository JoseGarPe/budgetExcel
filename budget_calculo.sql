DROP TABLE IF EXISTS `budget_calculo`;
CREATE TABLE `budget_calculo`  (
  `id_budget` int NOT NULL AUTO_INCREMENT,
  `dia` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fecha_par` date NOT NULL,
  `budget_valor`FLOAT(11,8) NOT NULL,
  `id_sucursal_par` int NOT NULL,
  PRIMARY KEY (`id_budget`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Procedure structure for insercion_
-- ----------------------------
DROP PROCEDURE IF EXISTS `insercion_`;
delimiter ;;
CREATE PROCEDURE `insercion_`(IN id_sucursal_parP INT(11), IN  diaP VARCHAR(11), IN budget_valorP FLOAT(11,8), IN fecha_parP DATE)
begin

DECLARE id_sucursal_var int;
DECLARE nombre_sucursal_var varchar(200);
DECLARE bandera int;
DECLARE existencia int;


IF(select exists(select * from budget_calculo where id_sucursal_par = id_sucursal_parP and fecha_par = fecha_parP) = 1)THEN

UPDATE budget_calculo SET budget_valor = budget_valorP WHERE  id_sucursal_par = id_sucursal_parP and fecha_par = fecha_parP;

ELSE
INSERT INTO budget_calculo VALUE(NULL,diaP,fecha_parP,budget_valorP,id_sucursal_parP);

-- SET id_sucursal_var=(SELECT id_sucursal FROM sucursales where nombre_sucursal = nombre_sucursal_par);
END IF;
end

SET FOREIGN_KEY_CHECKS = 1;
 USE budget_calculo;