<?php

/*
 * Author Thomas Beauchataud
 * Since 04/04/2022
 */

namespace TBCD\MessengerExtension\Message;

/**
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
interface DependentMessage
{

    /**
     * @return object[]
     */
    public function getMessageDependencies(): array;

}