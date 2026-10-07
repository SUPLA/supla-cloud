<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_calcfg_queue", indexes={@ORM\Index(name="IDX_CALCFG_QUEUE_USER", columns={"user_id"})})
 */
class CalcfgQueue {
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="IODevice")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $ioDevice;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /** @ORM\Column(type="text", nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"}) */
    private $queue;

    /** @ORM\Column(name="valid_until", type="utcdatetime", nullable=true) */
    private $validUntil;

    /** @ORM\Column(name="updated_at", type="utcdatetime") */
    private $updatedAt;
}
