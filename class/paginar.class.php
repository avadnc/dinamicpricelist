<?php

class Paginar
{

  
    private $limite = 5;
    private $pagina;
    private $consulta;
    private $total;
	private $data;


    public function getDatos($p)
    {
        $this->pagina = $p;
        $inicio = ($this->pagina - 1) * $this->limite;

        if ($inicio < 0) {
            $inicio = 0;
            $this->pagina = 1;
        }

        if ($inicio > ($this->total - $this->limite)) {
            $inicio = $this->total - $this->limite;
            $this->pagina = $this->pagina - 1;
        }

        $consultar = $this->consulta . ' limit ' . $inicio . ',' . $this->limite;
        $respuesta = $this->data;

        while ($row = $respuesta->fetch_assoc()) {
            $resultados[] = $row;
        }

        $result = new stdclass();
        $result->pagina = $this->pagina;
        $result->limite = $this->limite;
        $result->total = $this->total;
        $result->datos = $resultados;

        return $result;
    }

    public function crearLinks($enlaces)
    {
        $ultimo = ceil($this->total / $this->limite);
        $comienzo = (($this->pagina - $enlaces) > 0) ? $this->pagina - $enlaces : 1;
        $fin = (($this->pagina + $enlaces) < $ultimo) ? $this->pagina + $enlaces : $ultimo;
        $clase = ($this->pagina == 1) ? "disabled" : "";
        $html = '<li class="' . $clase . '"><a href="?limit=' . $this->limite . '&page=' . ($this->pagina - 1) . '">&laquo;</a></li>';

        if ($comienzo > 1) {
            $html .= '<li><a href="?limit=' . $this->limite . '&page=1">1</a></li>';
            $html .= '<li class="disabled"><span>...</span></li>';
        }

        for ($i = $comienzo; $i <= $fin; $i++) {
            $clase = ($this->pagina == $i) ? "active" : "";
            $html .= '<li class="' . $clase . '"><a href="?limit=' . $this->limite . '&page=' . $i . '">' . $i . '</a></li>';
        }

        if ($fin < $ultimo) {
            $html .= '<li class="disabled"><span>...</span></li>';
            $html .= '<li><a href="?limit=' . $this->limite . '&page=' . $ultimo . '">' . $ultimo . '</a></li>';
        }

        $clase = ($this->pagina == $ultimo) ? "disabled" : "enabled";
        $html .= '<li class="' . $clase . '"><a href="?limit=' . $this->limite . '&page=' . ($this->pagina + 1) . '">&raquo;</a></li>';
        return $html;
    }
}
