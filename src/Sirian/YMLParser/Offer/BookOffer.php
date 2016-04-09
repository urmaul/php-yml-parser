<?php

namespace Sirian\YMLParser\Offer;

class BookOffer extends Offer
{
	public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
}
