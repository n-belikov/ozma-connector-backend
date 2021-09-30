<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Ozma{
/**
 * App\Models\Ozma\Order
 *
 * @property int $id
 * @property string $connector_id
 * @property string $connector_type
 * @property int $ozma_id
 * @property int $ozma_stage_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereConnectorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereOzmaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereOzmaStageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ozma\Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @mixin \Eloquent
 */
	class IdeHelperUser extends \Eloquent {}
}

