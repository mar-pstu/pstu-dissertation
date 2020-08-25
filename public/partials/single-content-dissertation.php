<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


?>


<table>
	
	<tr>
		<th><?php _e( 'Автор', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_public_author]</td>
	</tr>

	<tr>
		<th><?php _e( 'Дата размещения на сайте', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_public_publication]</td>
	</tr>

	<tr>
		<th><?php _e( 'Защита диссертации состоится', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_public_protection empty=""] [dissertation_public_protection_time empty=""]</td>
	</tr>

	<tr>
		<th><?php _e( 'Диссертация', PSTU_DISSERTATION_NAME ); ?></th>
		<td><small>[dissertation_public_file_link]</small></td>
	</tr>

	<tr>
		<th><?php _e( 'Автореферат', PSTU_DISSERTATION_NAME ); ?></th>
		<td><small>[dissertation_public_abstract_link]</small></td>
	</tr>

	<tr>
		<th><?php _e( 'Официальные оппоненты', PSTU_DISSERTATION_NAME ); ?></th>
		<td>[dissertation_public_opponents]</td>
	</tr>

</table>