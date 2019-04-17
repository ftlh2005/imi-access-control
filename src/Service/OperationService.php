<?php
namespace Imi\AC\Service;

use Imi\Bean\Annotation\Bean;
use Imi\AC\Model\Operation;
use Imi\AC\Exception\OperationNotFound;
use Imi\AC\Model\Filter\OperationTreeItem;

/**
 * @Bean("ACOperationService")
 */
class OperationService
{
    /**
     * 获取操作
     *
     * @param int $id
     * @return \Imi\AC\Model\Operation
     */
    public function get($id)
    {
        return Operation::find($id);
    }

    /**
     * 创建操作权限
     *
     * @param string $name
     * @param string|null $code
     * @param int $parentId
     * @param int $index
     * @param string $description
     * @return \Imi\AC\Model\Operation
     */
    public function create($name, $code = null, $parentId = 0, $index = 0, $description = '')
    {
        $record = Operation::newInstance();
        $record->name = $name;
        $record->code = $code ?? $name;
        $record->parentId = $parentId;
        $record->index = $index;
        $record->description = $description;
        $result = $record->insert();
        if(!$result->isSuccess())
        {
            return false;
        }
        return $record;
    }

    /**
     * 更新操作权限
     *
     * @param int $id
     * @param string $name
     * @param string|null $code
     * @param int $parentId
     * @param int $index
     * @param string $description
     * @return boolean
     */
    public function update($id, $name, $code, $parentId = 0, $index = 0, $description = '')
    {
        $record = $this->get($id);
        if(!$record)
        {
            throw new OperationNotFound(sprintf('Operation id = %s does not found', $id));
        }
        $record->name = $name;
        $record->code = $code;
        $record->parentId = $parentId;
        $record->index = $index;
        $record->description = $description;
        return $record->update()->isSuccess();
    }

    /**
     * 删除操作
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $record = $this->get($id);
        if(!$record)
        {
            throw new OperationNotFound(sprintf('Operation id = %s does not found', $id));
        }
        return $record->delete()->isSuccess();
    }

    /**
     * 根据代码获取角色
     *
     * @param string $code
     * @return \Imi\AC\Model\Operation
     */
    public function getByCode($code)
    {
        return Operation::query()->where('code', '=', $code)->select()->get();
    }

    /**
     * 根据多个角色获取操作ID
     *
     * @param array $codes
     * @return int[]
     */
    public function selectIdsByCodes($codes)
    {
        if(!$codes)
        {
            return [];
        }
        return Operation::query()->field('id')->whereIn('code', $codes)->select()->getColumn();
    }

    /**
     * 根据id列表查询记录
     *
     * @param int $ids
     * @return \Imi\AC\Model\Operation[]
     */
    public function selectListByIds($ids)
    {
        if(!$ids)
        {
            return [];
        }
        return Operation::query()->whereIn('id', $ids)
                                 ->order('index')
                                 ->select()
                                 ->getArray();
    }

    /**
     * 转为树形
     *
     * @param \Imi\AC\Model\Operation[] $list
     * @return array
     */
    public function listToTree($list)
    {
        $tree = [];

		// 查询出所有分类记录
		$arr2 = array();
		// 处理成ID为键名的数组
		foreach($list as $item)
		{
			$arr2[$item->id] = OperationTreeItem::newInstance($item->toArray());
		}
		// 循环处理关联列表
		foreach($arr2 as $item)
		{
			if(isset($arr2[$item->parentId]))
			{
				$arr2[$item->parentId]->children[] = &$arr2[$item->id];
			}
			else
			{
				$tree[] = &$arr2[$item->id];
			}
		}
		return $tree;
    }

}