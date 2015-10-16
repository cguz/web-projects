<?php
class Categoria
{
    private $_id;

    private $_nombre;

    private $_grupo;

    private $_filtros;

    // Getters

    public function getId()
    {
        return $this->_id;
    }

    public function getNombre()
    {
        return $this->_nombre;
    }

    public function getGrupo()
    {
        return $this->_grupo;
    }

    public function getFiltros()
    {
        return $this->_filtros;
    }

    public function getAtributosClass()
    {
        $vars = array_keys(get_object_vars($this));
        
        foreach ($vars as $valor)
        {
            $atributos[substr($valor, 1)] = substr($valor, 1);
        }
        
        return $atributos;
    }

    public function getAtributos()
    {
        $atributos = array();
        
        $atributos_class = $this->getAtributosClass();
        
        foreach ($atributos_class as $valor)
        {
            eval('$atributos[$valor] = $this->get'.ucwords($valor).'();');
        }
        
        return $atributos;
    }

    // Setters

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function setNombre($nombre)
    {
        $this->_nombre = $nombre;
    }

    public function setGrupo($grupo)
    {
        $this->_grupo = $grupo;
    }

    public function setFiltros($filtros)
    {
        $this->_filtros = $filtros;
    }

    public function setAtributos($atributos)
    {
        if (is_array($atributos))
        {
            $atributos_class = $this->getAtributosClass();
            
            foreach ($atributos_class as $valor)
            {
                if (isset($atributos[$valor]))
                {
                    eval('$this->set'.ucwords($valor).'($atributos["'.$valor.'"]);');
                }
            }
        }
    }

    // Agrega un Categoria a la base de datos
    public function guardar($conexion = false)
    {
        if (is_object($conexion) && get_class($conexion) == 'Conexion' && $conexion->getEnlace())
        {
            $atributos_class = $this->getAtributosClass();
            unset($atributos_class['id']);
            
            $SQL = "INSERT INTO categoria (".implode(", ", $atributos_class).")\n VALUES ('".$this->getNombre()."', '".$this->getGrupo()."', '".$this->getFiltros()."')";
            
            return $conexion->ejecutar($SQL);
        }
        
        return false;
    }

    // Edita un Categoria de la base de datos
    public function editar($conexion = false)
    {
        if (is_object($conexion) && get_class($conexion) == 'Conexion' && $conexion->getEnlace())
        {
            $valores_editar = $this->getAtributos();
            
            if ($this->cargarBD($conexion))
            {
                $valores_bd = $this->getAtributos();
                
                $valores_sql = array();
                foreach ($valores_editar as $clave => $valor)
                {
                    if ($valores_bd[$clave] != $valor)
                    {
                        $valores_sql[] = "$clave = '$valor'";
                    }
                }
                
                if (count($valores_sql) != 0)
                {
                    $SQL = "UPDATE categoria SET ".implode(", ", $valores_sql)." WHERE id = ".$this->getId()."\n";
                }
                else
                {
                    return -1;
                }
                
                return $conexion->ejecutar($SQL);
            }
        }
        
        return false;
    }

    // Elimina un Categoria de la base de datos
    public function eliminar($conexion = false)
    {
        if (is_object($conexion) && get_class($conexion) == 'Conexion' && $conexion->getEnlace())
        {
            $SQL = "DELETE FROM categoria WHERE id = ".$this->getId()."\n";
            
            return $conexion->ejecutar($SQL);
        }
        
        return false;
    }

    // Busca un Categoria en la BD a trav�s del Id del objeto actual y coloca estos valores en los atributos del objeto
    public function cargarBD($conexion = false)
    {
        if (is_object($conexion) && get_class($conexion) == 'Conexion' && $conexion->getEnlace())
        {
            $atributos_class = $this->getAtributosClass();
            
            $SQL = "SELECT ".implode(", ", $atributos_class)." FROM categoria WHERE id = ".$this->getId()."\n";
            
            if ($conexion->consultar($SQL))
            {
                $row = $conexion->sacarRegistro();
                
                $this->setAtributos($row);
                
                return true;
            }
        }
        
        return false;
    }

    // Retorna un array de objetos Categoria con los valores en BD, se puede usar paginaci�n
    public function listar($conexion = false, $totalxpagina = 20, $pagina = 0, $ordenar_por = '', $ordenar_dir = '')
    {
        if (is_object($conexion) && get_class($conexion) == 'Conexion' && $conexion->getEnlace())
        {
            $SQL = 'SELECT count(id) AS total FROM categoria';
            
            if ($conexion->consultar($SQL))
            {
                $row = $conexion->sacarRegistro();
                
                $total_bd = $row["total"];
                
                $atributos_class = $this->getAtributosClass();
                
                $SQL = "SELECT ".implode(", ", $atributos_class)." FROM categoria";
                
                if ($ordenar_por != '' && $ordenar_dir != '')
                {
                    $SQL .= "\nORDER BY $ordenar_por $ordenar_dir";
                }
                
                if ($totalxpagina > 0 && $pagina >= 0)
                {
                    $SQL .= "\nLIMIT $pagina, $totalxpagina";
                }
                
                if ($conexion->consultar($SQL))
                {
                    $objetos = array();
                    $i = 0;
                    while ($row = $conexion->sacarRegistro())
                    {
                        $objetos[$i] = new Categoria();
                        $objetos[$i]->setAtributos($row);
                        $i++;
                    }
                    
                    if (count($objetos) > 0)
                    {
                        return array("total_bd" => $total_bd, "objetos" => $objetos);
                    }
                }
            }
        }
        
        return false;
    }
}
?>
