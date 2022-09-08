<?php

namespace App\Entity;

enum FileStatus: string
{

    case NEW = 'new';
    case UPLOADED_TO_SEARCH_INDEX = 'uploaded_to_search_index';

}