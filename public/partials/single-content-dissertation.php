<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


?>


<table>
	
	<tr>
		<th><?php _e( 'Автор', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_author]</td>
	</tr>

	<tr>
		<th><?php _e( 'Дата размещения на сайте', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_publication]</td>
	</tr>

	<tr>
		<th><?php _e( 'Защита диссертации состоится', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_protection empty=""] [dissertation_protection_time empty=""]</td>
	</tr>

	<tr>
		<th><?php _e( 'Диссертация', PSTU_DISSERTATION_NAME ); ?></th>
		<td><small>[dissertation_file]</small></td>
	</tr>

	<tr>
		<th><?php _e( 'Автореферат', PSTU_DISSERTATION_NAME ); ?></th>
		<td><small>[dissertation_abstract]</small></td>
	</tr>

	<tr>
		<th><?php _e( 'Официальные оппоненты', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_opponents]</td>
	</tr>

</table>