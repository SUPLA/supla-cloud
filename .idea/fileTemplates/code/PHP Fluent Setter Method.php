public function set${NAME}(#if (${SCALAR_TYPE_HINT})${SCALAR_TYPE_HINT} #else#end$${PARAM_NAME})#if(${RETURN_TYPE}): ${CLASS_NAME}#else#end
{
    $this->${FIELD_NAME} = $${PARAM_NAME};
    return $this;
}
