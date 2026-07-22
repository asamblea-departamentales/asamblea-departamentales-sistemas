<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute debe ser aceptado.',
    'accepted_if'          => ':attribute debe ser aceptado cuando :other es :value.',
    'active_url'           => ':attribute no es una URL válida.',
    'after'                => ':attribute debe ser una fecha posterior a :date.',
    'after_or_equal'       => ':attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                => ':attribute solo debe contener letras.',
    'alpha_dash'           => ':attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num'            => ':attribute solo debe contener letras y números.',
    'array'                => ':attribute debe ser un array.',
    'ascii'                => ':attribute solo debe contener caracteres alfanuméricos y símbolos.',
    'before'               => ':attribute debe ser una fecha anterior a :date.',
    'before_or_equal'      => ':attribute debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'array'   => ':attribute debe tener entre :min y :max elementos.',
        'file'    => ':attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => ':attribute debe estar entre :min y :max.',
        'string'  => ':attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean'              => ':attribute debe ser verdadero o falso.',
    'can'                  => ':attribute contiene un valor no autorizado.',
    'confirmed'            => ':attribute no coincide con la confirmación.',
    'date'                 => ':attribute no es una fecha válida.',
    'date_equals'          => ':attribute debe ser una fecha igual a :date.',
    'date_format'          => ':attribute no coincide con el formato :format.',
    'declined'             => ':attribute debe ser rechazado.',
    'declined_if'          => ':attribute debe ser rechazado cuando :other es :value.',
    'different'            => ':attribute y :other deben ser diferentes.',
    'digits'               => ':attribute debe tener :digits dígitos.',
    'digits_between'       => ':attribute debe tener entre :min y :max dígitos.',
    'dimensions'           => ':attribute tiene dimensiones de imagen no válidas.',
    'distinct'             => ':attribute tiene un valor duplicado.',
    'doesnt_end_with'      => ':attribute no debe terminar con uno de los siguientes valores: :values',
    'doesnt_start_with'    => ':attribute no debe comenzar con uno de los siguientes valores: :values',
    'email'                => ':attribute debe ser una dirección de correo válida.',
    'ends_with'            => ':attribute debe terminar con uno de los siguientes valores: :values',
    'enum'                 => ':attribute seleccionado no es válido.',
    'exists'               => ':attribute seleccionado no es válido.',
    'file'                 => ':attribute debe ser un archivo.',
    'filled'               => ':attribute es obligatorio.',
    'gt'                   => [
        'array'   => ':attribute debe tener más de :value elementos.',
        'file'    => ':attribute debe pesar más de :value kilobytes.',
        'numeric' => ':attribute debe ser mayor que :value.',
        'string'  => ':attribute debe tener más de :value caracteres.',
    ],
    'gte'                  => [
        'array'   => ':attribute debe tener :value elementos o más.',
        'file'    => ':attribute debe pesar :value kilobytes o más.',
        'numeric' => ':attribute debe ser mayor o igual que :value.',
        'string'  => ':attribute debe tener :value caracteres o más.',
    ],
    'image'                => ':attribute debe ser una imagen.',
    'in'                   => ':attribute seleccionado no es válido.',
    'in_array'             => ':attribute no existe en :other.',
    'integer'              => ':attribute debe ser un número entero.',
    'ip'                   => ':attribute debe ser una dirección IP válida.',
    'ipv4'                 => ':attribute debe ser una dirección IPv4 válida.',
    'ipv6'                 => ':attribute debe ser una dirección IPv6 válida.',
    'json'                 => ':attribute debe ser una cadena JSON válida.',
    'lt'                   => [
        'array'   => ':attribute debe tener menos de :value elementos.',
        'file'    => ':attribute debe pesar menos de :value kilobytes.',
        'numeric' => ':attribute debe ser menor que :value.',
        'string'  => ':attribute debe tener menos de :value caracteres.',
    ],
    'lte'                  => [
        'array'   => ':attribute debe tener :value elementos o menos.',
        'file'    => ':attribute debe pesar :value kilobytes o menos.',
        'numeric' => ':attribute debe ser menor o igual que :value.',
        'string'  => ':attribute debe tener :value caracteres o menos.',
    ],
    'mac_address'          => ':attribute debe ser una dirección MAC válida.',
    'max'                  => [
        'array'   => ':attribute no debe tener más de :max elementos.',
        'file'    => ':attribute no debe pesar más de :max kilobytes.',
        'numeric' => ':attribute no debe ser mayor que :max.',
        'string'  => ':attribute no debe tener más de :max caracteres.',
    ],
    'mimes'                => ':attribute debe ser un archivo con formato: :values.',
    'mimetypes'            => ':attribute debe ser un archivo con formato: :values.',
    'min'                  => [
        'array'   => ':attribute debe tener al menos :min elementos.',
        'file'    => ':attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':attribute debe ser al menos :min.',
        'string'  => ':attribute debe tener al menos :min caracteres.',
    ],
    'missing'              => ':attribute debe estar ausente.',
    'missing_if'           => ':attribute debe estar ausente cuando :other es :value.',
    'missing_unless'       => ':attribute debe estar ausente a menos que :other sea :value.',
    'missing_with'         => ':attribute debe estar ausente cuando :values está presente.',
    'missing_with_all'     => ':attribute debe estar ausente cuando :values está presente.',
    'not_in'               => ':attribute seleccionado no es válido.',
    'not_regex'            => 'El formato de :attribute no es válido.',
    'nullable'             => ':attribute puede ser nulo.',
    'numeric'              => ':attribute debe ser un número.',
    'password'             => 'La contraseña es incorrecta.',
    'present'              => ':attribute debe estar presente.',
    'prohibited'           => ':attribute está prohibido.',
    'prohibited_if'        => ':attribute está prohibido cuando :other es :value.',
    'prohibited_unless'    => ':attribute está prohibido a menos que :other esté en :values.',
    'prohibits'            => ':attribute prohíbe que :other esté presente.',
    'regex'                => 'El formato de :attribute no es válido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'required_array_keys'  => 'El campo :attribute debe contener entradas para: :values.',
    'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => 'El campo :attribute es obligatorio cuando :other es aceptado.',
    'required_unless'      => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without'     => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same'                 => ':attribute y :other deben coincidir.',
    'size'                 => [
        'array'   => ':attribute debe contener :size elementos.',
        'file'    => ':attribute debe pesar :size kilobytes.',
        'numeric' => ':attribute debe ser :size.',
        'string'  => ':attribute debe tener :size caracteres.',
    ],
    'starts_with'          => ':attribute debe comenzar con uno de los siguientes valores: :values',
    'string'               => ':attribute debe ser una cadena.',
    'timezone'             => ':attribute debe ser una zona horaria válida.',
    'unique'               => ':attribute ya ha sido tomado.',
    'uploaded'             => 'Error al subir :attribute.',
    'url'                  => ':attribute debe ser una URL válida.',
    'uuid'                 => ':attribute debe ser un UUID válido.',
    'ulid'                 => ':attribute debe ser un ULID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation attributes for use with your
    | Validation rules. These values may also be swapped between the
    | validation and error language lines of your application.
    |
    */

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Rules
    |--------------------------------------------------------------------------
    |
    | Here you may define custom validation rules for your application rules,
    | and they will be referenced below by their error message. This makes
    | it easy to define custom rules on the fly. For example, we will
    | define rules for password and email validation just like that.
    |
    */

    'rules' => [],

];
