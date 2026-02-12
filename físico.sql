CREATE TYPE Usuario_estado AS ENUM ('activo','inactivo','suspendido','retirado','bloqueado');
CREATE TABLE USUARIO (
                  Id_usuario         BIGSERIAL PRIMARY KEY,
                  Contrasena         VARCHAR(255) NOT NULL,
                  Fecha_nacimiento   DATE,
                  Genero             VARCHAR(20),
                  Celular            VARCHAR(30),
                  Correo             VARCHAR(255),
                  Cedula             VARCHAR(50),
                  Apellidos          VARCHAR(150),
                  Nombre             VARCHAR(150),
                  Estado_usuario     Usuario_estado NOT NULL DEFAULT 'activo'
                  );
				  

CREATE TABLE CONTACTO_EMERGENCIA (
                Id_contacto        BIGSERIAL PRIMARY KEY,
                Celular            VARCHAR(30) NOT NULL,
                Nombre             VARCHAR(150) NOT NULL,
                Usuario_id         BIGINT NOT NULL,
                CONSTRAINT fk_CONTACTO_USUARIO FOREIGN KEY (Usuario_id)
                REFERENCES USUARIO (Id_usuario)
                ON DELETE CASCADE
                ON UPDATE CASCADE
                );
				
CREATE TABLE AFILIADO (
                  Id_afiliado        BIGSERIAL PRIMARY KEY,
                  Usuario_id         BIGINT NOT NULL UNIQUE,
                  Fecha_creacion     TIMESTAMP WITH TIME ZONE DEFAULT now(),
                  CONSTRAINT fk_AFILIADO_USUARIO FOREIGN KEY (Usuario_id)
                  REFERENCES USUARIO (Id_usuario)
                  ON DELETE RESTRICT
                  ON UPDATE CASCADE
                );

CREATE TABLE ANTROPOMETRIA (
                  Id_antropometria   BIGSERIAL PRIMARY KEY,
                  Grasa_corporal     NUMERIC(5,2),
                  Altura_cm          NUMERIC(6,2),
                  Peso               NUMERIC(6,2),
                  Imc                NUMERIC(5,2),
                  Afiliado_id        BIGINT NOT NULL,
                  Fecha_medicion     TIMESTAMP WITH TIME ZONE DEFAULT now(),
                  CONSTRAINT fk_ANTRO_AFILIADO FOREIGN KEY (Afiliado_id)
                  REFERENCES AFILIADO (Id_afiliado)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE
                );

CREATE TYPE plan_tipo AS ENUM ('Mensual','Semestral','Anual');
CREATE TABLE PLAN (
                  Id_plan            BIGSERIAL PRIMARY KEY,
                  Tipo               plan_tipo NOT NULL,
                  Fecha_inicio       DATE NOT NULL,
                  Fecha_corte        DATE,
                  Fecha_fin          DATE,
                  Valor              NUMERIC(12,2) NOT NULL,
                  Afiliado_id        BIGINT NOT NULL,
                  CONSTRAINT fk_PLAN_AFILIADO FOREIGN KEY (Afiliado_id)
                  REFERENCES AFILIADO (id_afiliado)
                  ON DELETE RESTRICT
                  ON UPDATE CASCADE
                );
				
				
CREATE TYPE Estado AS ENUM ('Aprobado', 'Rechazado', 'En validación');
CREATE TABLE PAGOS (
                  Id_pago            BIGSERIAL PRIMARY KEY,
                  Fecha_cobro        TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
                  Estado             VARCHAR(50) NOT NULL,
                  Plan_id            BIGINT NOT NULL,
                  Monto              NUMERIC(12,2) NOT NULL,
                  Metodo_pago        VARCHAR(50),
                  Referencia         VARCHAR(200),
                  CONSTRAINT fk_pagos_plan FOREIGN KEY (plan_id)
                  REFERENCES plan (id_plan)
                  ON DELETE RESTRICT
                  ON UPDATE CASCADE
              );		  
			
CREATE TABLE PROFESIONAL (
                  Id_profesional     BIGSERIAL PRIMARY KEY,
                  Usuario_id         BIGINT UNIQUE,
                  Centro_id          BIGINT,
                  Especialidad       VARCHAR(150),
                  Fecha_ingreso      DATE,
                  CONSTRAINT fk_PROF_USUARIO FOREIGN KEY (Usuario_id)
                  REFERENCES USUARIO (Id_usuario)
                  ON DELETE SET NULL
                  ON UPDATE CASCADE
              );

CREATE TABLE TRABAJADOR (
                  Id_trabajador      BIGSERIAL PRIMARY KEY,
                  Usuario_id         BIGINT UNIQUE,
                  Centro_id          BIGINT,
                  Puesto             VARCHAR(150),
                  CONSTRAINT fk_trabajador_usuario FOREIGN KEY (usuario_id)
                  REFERENCES usuario (id_usuario)
                  ON DELETE SET NULL
                  ON UPDATE CASCADE
                );

CREATE TABLE ADMINISTRADOR (
                  Id_administrador   BIGSERIAL PRIMARY KEY,
                  Usuario_id         BIGINT UNIQUE,
                  Centro_id          BIGINT,
                  Nivel              VARCHAR(50),
                  CONSTRAINT fk_admin_usuario FOREIGN KEY (usuario_id)
                  REFERENCES usuario (id_usuario)
                  ON DELETE SET NULL
                  ON UPDATE CASCADE
                );

CREATE TABLE CENTRO_DEPORTIVO (
                  Id_centro          BIGSERIAL PRIMARY KEY,
                  Horario            VARCHAR(200),
                  Ubicacion          TEXT NOT NULL,
                  Nombre             VARCHAR(255)
                );

CREATE TABLE ACTIVIDAD_DEPORTIVA (
                Id_actividad       BIGSERIAL PRIMARY KEY,
                Fecha              TIMESTAMP WITH TIME ZONE NOT NULL,
                Tipo               VARCHAR(100),
                Duracion           INTEGER,
                Profesional_id     BIGINT,
                Centro_id          BIGINT NOT NULL,
                CONSTRAINT fk_ACTIVIDAD_PROF FOREIGN KEY (Profesional_id)
                REFERENCES PROFESIONAL (Id_profesional)
                ON DELETE SET NULL,
                CONSTRAINT fk_ACTIVIDAD_CENTRO FOREIGN KEY (Centro_id)
                REFERENCES CENTRO_DEPORTIVO (Id_centro)
                ON DELETE RESTRICT
                ON UPDATE CASCADE
              );

CREATE TABLE ASISTE_AFILIADO_ACTIVIDAD (
                Afiliado_id        BIGINT NOT NULL,
                Actividad_id       BIGINT NOT NULL,
                Fecha_asistencia   TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
                Fecha_inscripcion  TIMESTAMP WITH TIME ZONE,
                PRIMARY KEY (afiliado_id, actividad_id, fecha_asistencia),
                CONSTRAINT fk_ASISTE_AFILIADO FOREIGN KEY (Afiliado_id)
                REFERENCES AFILIADO (Id_afiliado)
                ON DELETE RESTRICT,
                CONSTRAINT fk_ASISTE_ACTIVIDAD FOREIGN KEY (Actividad_id)
                REFERENCES ACTIVIDAD_DEPORTIVA (Id_actividad)
                ON DELETE RESTRICT
              );
				
CREATE TABLE INGRESA_AFILIADO_CENTRO (
                Afiliado_id        BIGINT NOT NULL,
                Centro_id          BIGINT NOT NULL,
                Fecha_ingreso      TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
                PRIMARY KEY (afiliado_id, centro_id, fecha_ingreso),
                CONSTRAINT fk_INGRESA_AFILIADO FOREIGN KEY (Afiliado_id)
                REFERENCES AFILIADO (Id_afiliado)
                ON DELETE RESTRICT,
                CONSTRAINT fk_INGRESA_CENTRO FOREIGN KEY (Centro_id)
                REFERENCES CENTRO_DEPORTIVO (Id_centro)
                ON DELETE RESTRICT
              );


CREATE TABLE EJERCICIO (
                Id_ejercicio       BIGSERIAL PRIMARY KEY,
                Tipo               VARCHAR(100) NOT NULL,
                Guia               TEXT
              );

CREATE TABLE RUTINA (
                Id_rutina          BIGSERIAL PRIMARY KEY,
                Nombre             VARCHAR(200),
                Sesiones_totales   INTEGER,
                Sesiones_restantes INTEGER,
                Afiliado_id        BIGINT NOT NULL,
                Profesional_id     BIGINT,
                CONSTRAINT fk_RUTINA_AFILIADO FOREIGN KEY (Afiliado_id)
                REFERENCES AFILIADO (Id_afiliado)
                ON DELETE CASCADE,
                CONSTRAINT fk_RUTINA_PROF FOREIGN KEY (Profesional_id)
                REFERENCES Profesional (Id_profesional)
                ON DELETE SET NULL
              );			  
				
CREATE TABLE RUTINA_CONTIENE_EJERCICIO (
                rutina_id          BIGINT NOT NULL,
                ejercicio_id       BIGINT NOT NULL,
                Repeticiones       INTEGER,
                Series             INTEGER,
                PRIMARY KEY (rutina_id, ejercicio_id),
                CONSTRAINT fk_RCE_RUTINA FOREIGN KEY (Rutina_id)
                REFERENCES RUTINA (Id_rutina)
                ON DELETE CASCADE,
                CONSTRAINT fk_RCE_EJERCICIO FOREIGN KEY (Ejercicio_id)
                REFERENCES EJERCICIO (Id_ejercicio)
                ON DELETE RESTRICT
              );
			  
CREATE TABLE MAQUINA (
                Id_maquina         BIGSERIAL PRIMARY KEY,
                Nombre             VARCHAR(150),
                Estado             VARCHAR(50),
                Ubicacion          VARCHAR(200),
                Administrador_id   BIGINT,
                Centro_id          BIGINT,
                Ejercicio_id       BIGINT,
                CONSTRAINT fk_MAQUINA_ADMINB FOREIGN KEY (Administrador_id)
                REFERENCES ADMINISTRADOR (Id_administrador)
                ON DELETE SET NULL,
                CONSTRAINT fk_MAQUINA_CENTRO FOREIGN KEY (Centro_id)
                REFERENCES CENTRO_DEPORTIVO (Id_centro)
                ON DELETE RESTRICT,
                CONSTRAINT fk_MAQUINA_EJERCICIO FOREIGN KEY (Ejercicio_id)
                REFERENCES EJERCICIO (Id_ejercicio)
                ON DELETE SET NULL
                );


			