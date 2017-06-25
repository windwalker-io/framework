<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO;

use Psr\Http\Message\ServerRequestInterface;
use Windwalker\IO\Filter\NullFilter;

/**
 * Class PsrInput
 *
 * @property    Input          $get
 * @property    Input          $post
 * @property    Input          $put
 * @property    Input          $patch
 * @property    Input          $delete
 * @property    Input          $link
 * @property    Input          $unlink
 * @property    Input          $request
 * @property    Input          $server
 * @property    Input          $env
 * @property    PsrHeaderInput $header
 * @property    PsrFilesInput  $files
 * @property    CookieInput    $cookie
 *
 * @since 3.0
 */
class PsrInput extends Input
{
	/**
	 * Create Input from Psr ServerRequest
	 *
	 * @param   ServerRequestInterface $request
	 *
	 * @return  static
	 */
	public static function create(ServerRequestInterface $request)
	{
		$method   = strtolower($request->getMethod());
		$query    = $request->getQueryParams();
		$post     = $request->getParsedBody();

		$input = new PsrInput(array_merge($query, $post));
		$input->setMethod($request->getMethod());

		$filter = $input->getFilter() instanceof NullFilter ? null : $input->getFilter();

		// Sort by importance
		$input->get     = new Input($query, $filter);
		$input->post    = new Input($method === 'post'   ? $post : [], $filter);
		$input->files   = new PsrFilesInput($request->getUploadedFiles(),  $filter);
		$input->put     = new Input($method === 'put'    ? $post : [], $filter);
		$input->patch   = new Input($method === 'patch'  ? $post : [], $filter);
		$input->delete  = new Input($method === 'delete' ? $post : [], $filter);
		$input->link    = new Input($method === 'link'   ? $post : [], $filter);
		$input->unlink  = new Input($method === 'unlink' ? $post : [], $filter);
		$input->request = new Input(array_merge($query, $post));

		// Super Globals
		$input->server = new Input($request->getServerParams(),       $filter);
		$input->header = new PsrHeaderInput($request->getHeaders(),   $filter);
		$input->cookie = new CookieInput($request->getCookieParams(), $filter);

		return $input;
	}
}
