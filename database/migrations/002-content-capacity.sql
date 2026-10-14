-- Ampliación de capacidad, sin eliminar ni transformar los textos existentes.
-- Seleccione primero la base de esta aplicación. 20.000 caracteres utf8mb4 pueden exceder TEXT.
ALTER TABLE news MODIFY content MEDIUMTEXT NOT NULL;
