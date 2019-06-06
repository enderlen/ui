<?php
/**
 * A Card container for Card Lister.
 */

namespace atk4\ui;

use atk4\data\Model;

class CardHolder extends View
{
    public $ui = 'card';

    public $defaultTemplate = 'card-holder.html';

    /** @var null|View A View that hold the image. */
    public $imageContainer = null;

    /** @var null|string|Image A path to the image src or the image view. */
    public $image = null;

    /** @var null|View A View for displaying main content of the card. */
    public $cardContent = null;

    /** @var null | View The extra content view container for the card. */
    public $extraContainer = null;

    /** @var null|string|View A description inside the Card content. */
    public $description = null;

    /** @var null|array|Button A button or an array of Buttons */
    public $buttons = null;

    /** @var null|View The button Container for Button */
    public $btnContainer = null;

    /** @var string Table css class */
    public $tableClass = 'ui fixed small';

    /** @var bool Display model field as table inside card holder content */
    public $useTable = false;

    /** @var array Array of columns css wide classes */
    protected $words = [
        '', 'fluid', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
        'thirteen', 'fourteen', 'fifteen', 'sixteen',
    ];

    /** @var int The number of buttons */
    private $btnCount = 0;

    public function init()
    {
        parent::init();

        if (!$this->cardContent) {
            $this->cardContent = $this->add(['View', 'class' => ['content']]);
        }

        if ($this->imageContainer) {
            $this->add($this->imageContainer, 'Image');
        }

        if ($this->description) {
            $this->addDescription($this->description);
        }

        if ($this->image) {
            $this->addImage($this->image);
        }

        if ($this->buttons) {
            $this->addButton($this->buttons);
        }
    }

    /**
     * Add Content to card.
     *
     * @param View $view
     *
     * @throws Exception
     *
     * @return View|null
     */
    public function addContent(View $view)
    {
        if (!$this->cardContent) {
            throw new Exception('Content can be added to Card content only. Setup card content property.');
        }

        $this->cardContent->add($view);

        return $this->cardContent;
    }

    /**
     * Set model.
     *
     * @param \atk4\data\Model $m           The model.
     * @param array            $columns     An array of fields name to display in content.
     * @paran array            $extras      An array of fields name to display in extra content.
     *
     * @throws Exception
     *
     * @return \atk4\data\Model|void
     */
    public function setModel(Model $m, $columns = [], $extras = [])
    {
        $m = parent::setModel($m);
        if (!$m->loaded()) {
            throw new Exception('Model need to be loaded.');
        }

        if ($this->useTable) {
            $m = $this->setCardModel($m, $columns);
        } else {
            $this->setContentModel($m, $columns);
        }

        if (!empty($extras)) {
            $this->setExtra($m, $extras);
        }

        return $m;
    }

    /**
     * Set content using model field.
     *
     * @param Model $m          The model
     * @param array $columns    An array of fields name.
     *
     * @throws Exception
     */
    private function setContentModel($m, $columns)
    {
        $this->addContent(new Header([$m->getTitle()]));

        foreach ($columns as $column) {
            $this->addDescription($m->get($column));
        }

    }

    /**
     * Set content using table Card View model field.
     *
     * @param Model $m          The model
     * @param array $columns    An array of fields name.
     *
     * @return Model
     * @throws Exception
     */
    private function setCardModel($m, $columns)
    {
        $c = new Card(['class' => $this->tableClass]);
        $c->init();
        $m = $c->setModel($m, $columns);
        $this->addContent($c);

        return $m;
    }

    /**
     * Set extra content using model field.
     *
     * @param Model $m          The model
     * @param array $extras     An array of fields name.
     *
     * @throws Exception
     */
    private function setExtra($m, $extras)
    {
        foreach ($extras as $extra) {
            $this->addExtraContent(new View([$m->get($extra)]));
        }
    }

    /**
     * Add Description to card content.
     *
     * @param string|View $description
     *
     * @throws Exception
     *
     * @return View|string|null The description to add.
     */
    public function addDescription($description)
    {
        $view = null;

        if (!$this->cardContent) {
            throw new Exception('Description can be added to Card content only. Setup card content property.');
        }

        if (is_string($description)) {
            $view = $this->cardContent->add(new View([$description, 'class' => ['description']]));
        } elseif ($description instanceof View) {
            $view = $this->cardContent->add($description)->addClass('description');
        }

        return $view;
    }

    /**
     * Add Extra content to the Card.
     * Extra content is added at the bottom of the card.
     *
     * @param View $view
     *
     * @throws Exception
     *
     * @return View
     */
    public function addExtraContent(View $view)
    {
        if (!$this->extraContainer) {
            $this->extraContainer = $this->add(['View', 'class' => ['extra content']], 'ExtraContent');
        }

        return $this->extraContainer->add($view);
    }

    /**
     * Add image to card.
     *
     * @param string|Image $img
     *
     * @throws Exception
     *
     * @return View|null
     */
    public function addImage($img)
    {
        if (!$this->imageContainer) {
            $this->imageContainer = $this->add(['View', 'class' => ['image']], 'Image');
        }

        if (is_string($img)) {
            $this->imageContainer->add(new Image([$img]));
        } else {
            $this->imageContainer->add($img);
        }

        return $this->imageContainer;
    }

    /**
     * Add button(s) to card.
     *
     * @param array|Button $buttons A Button or array of Button.
     * @param bool         $isFluid Make the buttons spread evenly in Card.
     *
     * @throws Exception
     *
     * @return View|null
     */
    public function addButton($buttons, $isFluid = true)
    {
        if (!$this->btnContainer) {
            $this->btnContainer = $this->addExtraContent(new View(['ui' => 'buttons']));
        }

        if ($isFluid && $this->btnCount > 0) {
            $this->btnContainer->removeClass($this->words[$this->btnCount]);
        }

        if (is_array($buttons)) {
            foreach ($buttons as $btn) {
                $this->btnContainer->add($btn);
                $this->btnCount++;
            }
        } else {
            $this->btnContainer->add($buttons);
            $this->btnCount++;
        }

        if ($isFluid && $this->btnCount > 0) {
            $this->btnContainer->addClass($this->words[$this->btnCount]);
        }

        return $this->btnContainer;
    }
}