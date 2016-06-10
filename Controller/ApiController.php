<?php

/*
 * This file is part of the distributed-configuration-bundle package
 *
 * Copyright (c) 2016 Guillaume Cavana
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Guillaume Cavana <guillaume.cavana@gmail.com>
 */

namespace Maikuro\DistributedConfigurationBundle\Controller;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Maikuro\DistributedConfigurationBundle\Form\KeyValueType;
use Maikuro\DistributedConfigurationBundle\Model\KeyValue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\KeyValueStore\Api\WriteException;

/**
 * Class ApiController.
 */
class ApiController extends Controller
{
    /**
     * Get a value from a key.
     *
     * @param Request $request
     * @param string  $key
     *
     * @throw Webmozart\KeyValueStore\Api\NoSuchKeyException
     *
     * @return KeyValue
     */
    public function getAction(Request $request, $key)
    {
        $storeHandler = $this->get('maikuro_distributed_configuration.store_handler');

        $view = View::create()
            ->setStatusCode(Codes::HTTP_OK)
            ->setData($storeHandler->get($key));

        return $this->getViewHandler()->handle($view, $request);
    }

    /**
     * Create a key value.
     *
     * @param Request $request
     *
     * @return mixed|FormInterface
     */
    public function createAction(Request $request)
    {
        return $this->handleForm(
            $this->createRestForm(new KeyValueType(), new KeyValue()),
            $request
        );
    }

    /**
     * Create a key.
     *
     * @param Request $request
     * @param string  $key
     *
     * @return Response
     */
    public function updateAction(Request $request, $key)
    {
        $storeHandler = $this->get('maikuro_distributed_configuration.store_handler');
        $keyValue = $storeHandler->get($key);

        return $this->handleForm($this->createRestForm(
            new KeyValueType(),
            $keyValue,
            ['method' => $request->getMethod()]
        ), $request);
    }

    /**
     * Delete a key.
     *
     * @param Request $request
     * @param string  $key
     *
     * @throw Webmozart\KeyValueStore\Api\WriteException
     *
     * @return Response
     */
    public function deleteAction(Request $request, $key)
    {
        $view = View::create()
            ->setStatusCode(Codes::HTTP_NO_CONTENT)
        ;

        $storeHandler = $this->get('maikuro_distributed_configuration.store_handler');
        $storeHandler->remove($key);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * handleForm.
     *
     * @param FormInterface $form
     * @param Request       $request
     *
     * @return Response
     */
    protected function handleForm(FormInterface $form, Request $request)
    {
        $method = $request->getMethod();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            $storeHandler = $this->get('maikuro_distributed_configuration.store_handler');
            $storeHandler->flush($entity);

            if (in_array($method, ['PUT', 'PATCH'])) {
                return $this->getViewHandler()->handle($this->onUpdateSuccess($entity));
            }

            return $this->getViewHandler()->handle($this->onCreateSuccess($entity));
        }

        return $this->getViewHandler()->handle($this->onError($form));
    }

    /**
     * onUpdateSuccess.
     *
     * @param KeyValue $entity
     *
     * @return View
     */
    protected function onUpdateSuccess(KeyValue $entity)
    {
        return  View::create()
                    ->setStatusCode(Codes::HTTP_NO_CONTENT)
                ;

        return $view;
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param KeyValue $entity
     *
     * @return View
     */
    protected function onCreateSuccess(KeyValue $entity)
    {
        $view = View::create()
            ->setStatusCode(Codes::HTTP_CREATED)
            ->setData(
                $entity
            )
        ;

        return $view;
    }

    /**
     * Returns a HTTP_BAD_REQUEST response when the form submission fails.
     *
     * @param FormInterface $form
     *
     * @return View
     */
    protected function onError(FormInterface $form)
    {
        $view = View::create()
            ->setStatusCode(Codes::HTTP_BAD_REQUEST)
            ->setData($form)
        ;

        return $view;
    }

    /**
     * createRestForm.
     *
     * @param string $type
     * @param mixed  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    protected function createRestForm($type, $data = null, array $options = [])
    {
        // BC >= 2.8
        if ('Symfony\Component\Form\Extension\Core\Type\FormType' === $type->getParent()) {
            $type = get_class($type);
        }

        return $this->container->get('form.factory')->createNamed(null, $type, $data, $options);
    }

    /**
     * ViewHandler.
     */
    protected function getViewHandler()
    {
        return $this->get('fos_rest.view_handler');
    }
}
