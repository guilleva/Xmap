<?php

class JFactory
{
    public static function getApplication()
    {
        return new JApplicationSite;
    }

    public static function getLanguage()
    {
        return new JLanguage;
    }
}
